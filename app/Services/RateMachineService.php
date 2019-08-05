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

    /**
     * Init machine for Client
     */
    public function initMachine(Client $client) {
        $this->client = $client;
    }

    /**
     * Calculate cdr row 
     */
    public function calculateCall(Cdr $cdr) {
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