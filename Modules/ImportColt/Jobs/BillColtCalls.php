<?php

namespace Modules\ImportColt\Jobs;

use Utils;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

use Modules\ImportColt\Services\ColtService;

class BillColtCalls implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected $importColtId;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\User $user, int $importColtId)
    {
        $this->user = $user;
        $this->importColtId = $importColtId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ColtService $coltService)
    {
        Utils::logColtService('info', 'Start bill cdrs for import_colt_id = ' . $this->importColtId . ' ...');
        try {
            \Auth::setUser($this->user);
            $result = $coltService->billCdr($this->importColtId, true);
            echo 'Invoices were billed' . PHP_EOL;
            Utils::logColtService('info', $result['count'] .' Invoices were billed on sum ' . $result['sum']);
        } catch(\Exception $e) {
           echo 'ERROR:' . $e->getMessage() . PHP_EOL;
           echo 'File: ' . $e->getFile() . PHP_EOL;
           echo 'Line: ' . $e->getLine() . PHP_EOL;
           echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
           Utils::logColtService('error', 'ERROR:' . $e->getMessage());
           Utils::logColtService('error', 'Trace:' . $e->getTraceAsString());           
        }
    }
}
