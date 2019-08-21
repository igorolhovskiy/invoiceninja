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


    private function mediateDuration($duration, $initSeconds, $incrementSeconds) {
        
        if ($duration <= 0) {
            return 0;
        }
        if ($duration <= $initSeconds) {
            return $initSeconds;
        }

        $duration_corrected = $duration - $initSeconds;
        $extra_add = ($duration_corrected % $incrementSeconds == 0) ? 0 : 1;
        
        $duration_corrected = $init_inc + (floor($duration_corrected / $incrementSeconds) + $extra_add) * $incrementSeconds;

        return $duration_corrected;
    }
    
    public function __construct($precision = 2) {
        // Yes, I know it's like this by default, but I want it to be explicit.
        $this->resetMachine($precision);
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
                ->all();
        }
        dd($this->packages->toArray());
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

        $duration_mediated = $this->mediateDuration($cdr->dur, $rate->init_seconds, $rate->increment_seconds);

        // Looking for packages:
        

        // if ($package) {
        //    echo "Found package: name:{$package->name}, minutes:{$package->amount_of_minutes}, price:{$package->price}" . PHP_EOL;
        // } else {
        //    echo 'No one Packages found' . PHP_EOL;
        // }
        $cost = rand(0, 75);

        $cdr->cost = $cost;
        $cdr->done = 1;

        return $cdr;
    }

    /** 
     * Reset all counters
     */
    public function resetMachine($precision = 2) {
        $this->client = NULL;
        $this->coltInvoice = NULL;
        $this->packages = NULL;
        $this->rateId = NULL;
        $this->precision = $precision;
    }
}