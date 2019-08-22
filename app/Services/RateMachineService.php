<?php

namespace App\Services;

use Illuminate\Support\Collection;

use \App\Models\Cdr;
use \App\Models\Client;

/**
 * Rate machine servie
 */
class RateMachineService
{
    protected $client;
    protected $coltInvoice;
    protected $packages; // array of telco package id and it's info
    protected $rateId; // id of telco rate
    protected $precision; // Number of digits after the dot
    protected $defaultInitIncrementSeconds;

    private function mediateDuration($duration, $initSeconds, $incrementSeconds) {
        
        if ($duration <= 0) {
            return 0;
        }
        if ($duration <= $initSeconds) {
            return $initSeconds;
        }

        $durationCorrected = $duration - $initSeconds;
        $extraAdd = ($durationCorrected % $incrementSeconds == 0) ? 0 : 1;
        
        $durationCorrected = $initSeconds + (floor($durationCorrected / $incrementSeconds) + $extraAdd) * $incrementSeconds;

        return $durationCorrected;
    }
    
    public function __construct($precision = 2, $defaultInitIncrementSeconds = 1) {
        // Yes, I know it's like this by default, but I want it to be explicit.
        $this->resetMachine($precision, $defaultInitIncrementSeconds);
    }
    /**
     * Init machine for Client
     */
    public function initMachine(Client $client) {
        if (!$client) {
            return False;
        }
        $this->client = $client;
        $this->coltInvoice = \App\Models\Invoice
            ::with('invoice_items')
            ->where('client_id', $client->id)
            ->where('invoice_category_id', INVOICE_ITEM_CATEGORY_COLT)
            ->first();
        if (!$this->coltInvoice) {
            return False;
        }
        $packageIds = $this->coltInvoice
            ->invoice_items->where('product_type', 'telcopackages')
            ->map(function($item) {
                return $item->product_id;
            })->all();
        
        if (count($packageIds) > 0) {
            $this->packages = \Modules\Telcopackages\Models\Telcopackages
                ::whereIn('id', $packageIds)
                ->with('codes')
                ->get();
        }

        $rateItem = $this->coltInvoice
            ->invoice_items->where('product_type', 'telcorates')
            ->first();
        $this->rateId = $rateItem ? $rateItem->product_id : null;
        if ($this->rateId) {
            return True;
        }
        return False;
    }

    /**
     * Calculate cdr row 
     */
    public function calculateCall(Cdr $cdr) {
        // Looking for rate for dst :
        $rate = \Modules\Telcorates\Models\TelcorateCode
            ::where('telcorate_id', $this->rateId)
            ->whereRaw("instr('{$cdr->dst}', code) = 1")
            ->orderByRaw('length(code) desc')
            ->first();
        if (!$rate) {
            echo 'Rate not found' . PHP_EOL;
            $cdr->cost = 0;
            $cdr->status = CDR_STATUS_RATE_NOT_FOUND;
            $cdr->done = 1;
            
            return $cdr;
        }
        echo "Found rate: code:{$rate->code}, second:{$rate->init_seconds}, increment: {$rate->increment_seconds}, rate:{$rate->rate}" . PHP_EOL;

        if ($rate->init_seconds <= 0) {
            $rate->init_seconds = $this->defaultInitIncrementSeconds;
        }
        if ($rate->increment_seconds <= 0) {
            $rate->increment_seconds = $this->defaultInitIncrementSeconds;
        }

        $durationMediated = $this->mediateDuration($cdr->dur, $rate->init_seconds, $rate->increment_seconds);
        $cdrStatus = '';

        // Processing packages
        if ($this->packages) {

            $packageId = NULL;

            $codeToSearch = $cdr->dst;

            // Cycling through number
            while (strlen($codeToSearch) >= 1) {
                foreach ($this->packages as $id => $package) {

                    $found_code = $package->codes->first(function ($value) use ($codeToSearch) {
                            return $value->code == $codeToSearch;
                        }
                    );

                    if (!isset($package->amount_of_seconds)) {
                        $package->amount_of_seconds = $package->amount_of_minutes * 60;
                    }

                    if ($found_code && $package->amount_of_seconds > 0) {// Save further info only on packages that have amount of minutes left
                        $packageId = $id;
                        echo "Found active package: id:{$id}, Name:{$package->name}, Sec left:{$package->amount_of_seconds}" . PHP_EOL;
                        break 2; // Exit both while and foreach
                    }
                }
                $codeToSearch = mb_substr($codeToSearch, 0, -1);
            }

            if ($packageId) {
                // Package for this call found
                $packageSecondsLeft = $this->packages[$packageId]->amount_of_seconds;

                if ($durationMediated <= $packageSecondsLeft) { // We have more seconds than call is done

                    $this->packages[$packageId]->amount_of_seconds = $packageSecondsLeft - $durationMediated;
                    $cdrStatus = CDR_STATUS_PACKAGE_CALL;
                    $durationMediated = 0;
                    echo "Call to $cdr->dst is within package " . $this->packages[$packageId]->name . PHP_EOL;

                } else { // We have combined call of PACKAGE + STANDARD

                    $this->packages[$packageId]->amount_of_seconds = 0;
                    $cdrStatus = CDR_STATUS_PACKAGE_PLUS;
                    $durationMediated = $durationMediated - $packageSecondsLeft; // Reduce medirated duration on package size
                    echo "Call to $cdr->dst is partial within package " . $this->packages[$packageId]->name . PHP_EOL;
                }
            }
        }

        if ($durationMediated == 0) {
            $cdr->cost = 0;
            $cdr->status = $cdrStatus;
            $cdr->done = 1;
            return $cdr;
        }
        
        $call_cost = round((float)($durationMediated / 60) * (float)$rate->rate, $this->precision);

        $cdr->cost = $call_cost;
        $cdr->status = $cdrStatus . CDR_STATUS_STANDARD;
        $cdr->done = 1;

        echo "Call to $cdr->dst cost is $call_cost". PHP_EOL;

        return $cdr;
    }

    /** 
     * Reset all counters
     */
    public function resetMachine($precision = 2, $defaultInitIncrementSeconds = 1) {
        $this->client = NULL;
        $this->coltInvoice = NULL;
        $this->packages = NULL;
        $this->rateId = NULL;
        $this->precision = $precision;
        $this->defaultInitIncrementSeconds = $defaultInitIncrementSeconds;
    }
}