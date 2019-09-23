<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\RateMachineService;

class StartRateMachine extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telco:start-rate-machine {clientId : The ID of client}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Start Rate Machine for Client';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle(RateMachineService $rateMachineService)
    {
        $clientId = $this->argument('clientId');
        $client = \App\Models\Client::find($clientId);
        if (!$client) {
            $this->error('Client ' . $clientId . ' is not found');
            return false;
        } 
        if (!$this->confirm('Do you wish to rate the cdr for client ' . $client->name .' ?')) {
            return true;
        }
        $cdrs = \App\Models\Cdr
            ::where('client_id', $client->id)
            //->where('done', 0)
            ->orderBy('id')
            ->get();
        
        $rateMachineService->initMachine($client);
        foreach ($cdrs as $cdr) {
            $cdr = $rateMachineService->calculateCall($cdr);
            $this->info("Date:{$cdr->datetime} did:{$cdr->did}, dst:{$cdr->dst}, dur:{$cdr->dur}, cost:{$cdr->cost}, status:{$cdr->status}, done:{$cdr->done}");
        }
    }
}
