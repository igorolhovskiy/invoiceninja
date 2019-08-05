<?php

namespace Modules\ImportColt\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Bus\Queueable;

use Illuminate\Support\Facades\Mail;

use Modules\ImportColt\Models\ImportColt;
use Modules\ImportColt\Services\ColtService;

use Modules\ImportColt\Jobs\RateColtCalls;

class ParseColt implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Queueable;

    protected $importColt;
    protected $user;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(\App\Models\User $user, ImportColt $importColt)
    {   
        $this->user = $user;
        $this->importColt = $importColt;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ColtService $coltService)
    {
        $filePath = $this->importColt->file_path;
        echo "Colt file path:" . $filePath . PHP_EOL;
        try {
            \Auth::setUser($this->user);
            $coltData = $coltService->parseColtFile($filePath);
            echo 'Successfuly parsed ' . count($coltData) . ' rows' . PHP_EOL;
            echo 'Build cdrs ' . PHP_EOL;
            $coltService->buildCdr($coltData, $this->importColt->id);
            echo 'cdrs is builded' . PHP_EOL;
            dispatch(new RateColtCalls(\Auth::user(), $this->importColt->id));
            
        } catch(\Exception $e) {
           echo 'ERROR:' . $e->getMessage() . PHP_EOL;
           echo 'File: ' . $e->getFile() . PHP_EOL;
           echo 'Line: ' . $e->getLine() . PHP_EOL;
           echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;
        }

    }
}
