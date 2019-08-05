<?php

namespace Modules\ImportColt\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

use Modules\ImportColt\Services\ColtService;

use Modules\ImportColt\Jobs\BillColtCalls;

class RateColtCalls implements ShouldQueue
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
        try {
            \Auth::setUser($this->user);
            $coltService->rateColtCalls($this->importColtId);
            echo 'cdrs are rated' . PHP_EOL;
            dispatch(new BillColtCalls(\Auth::user(), $this->importColtId));
        } catch(\Exception $e) {
           echo 'ERROR:' . $e->getMessage() . PHP_EOL;
           echo 'File: ' . $e->getFile() . PHP_EOL;
           echo 'Line: ' . $e->getLine() . PHP_EOL;
           echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
        }
    }
}
