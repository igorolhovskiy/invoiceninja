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
    protected $packageIds; // array of telco package id
    protected $rateId; // id of telco rate
    /**
     * Init machine for Client
     */
    public function initMachine(Client $client) {
        $this->client = $client;
        $this->coltInvoice = \App\Models\Invoice
            ::with('invoice_items')
            ->where('client_id', $client->id)
            ->where('invoice_category_id', INVOICE_ITEM_CATEGORY_COLT)
            ->first();
        $this->packageIds = $this->coltInvoice
            ->invoice_items->where('product_type', 'telcopackages')
            ->map(function($item) {
                return $item->product_id;
            })->all();
        $rateItem = $this->coltInvoice
            ->invoice_items->where('product_type', 'telcorates')->first();
        $this->rateId = $rateItem ? $rateItem->product_id : null;
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
        if ($rate) {
            echo "Found rate: code:{$rate->code}, second:{$rate->init_seconds}, increment: {$rate->increment_seconds}, rate:{$rate->rate}" . PHP_EOL;
        } else {
            echo 'Rate not found' . PHP_EOL;
        }
        // Looking for packages:
        $package = \Modules\Telcopackages\Models\Telcopackages
            ::whereIn('id', $this->packageIds)
            ->whereHas('codes', function($query) use ($cdr) {
                $query->whereRaw("instr('{$cdr->dst}', code) = 1");
            })
            ->first();
        if ($package) {
            echo "Found package: name:{$package->name}, minutes:{$package->amount_of_minutes}, price:{$package->price}" . PHP_EOL;
        } else {
            echo 'No one Packages found' . PHP_EOL;
        }
        $cost = rand(0, 75);

        $cdr->cost = $cost;
        $cdr->done = 1;

        return $cdr;
    }

    /** 
     * Reset all counters
     */
    public function resetMachine() {

    }
}