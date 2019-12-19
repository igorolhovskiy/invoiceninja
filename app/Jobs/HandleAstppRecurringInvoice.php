<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use App\Jobs\SendInvoiceEmail;
use App\Models\Invoice;
use App\Services\AstppService;
use App\Services\LogService;

use Auth;
use Utils;

class HandleAstppRecurringInvoice implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $recurInvoice;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Invoice $invoice)
    {
        $this->recurInvoice = $invoice;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(AstppService $astppService, LogService $logService)
    {
        echo date('r'), ' Processing ASTPP Recuring invoice: ', $this->recurInvoice->id, PHP_EOL;
        Utils::logAstppService('info', '====================================');
        Utils::logAstppService('info', 'Processing ASTPP Recuring invoice: '. $this->recurInvoice->id);
        $account = $this->recurInvoice->account;
        $client = $this->recurInvoice->client;
        echo date('r'), ' Client: ', $client->name, PHP_EOL;
        Utils::logAstppService('info', 'Client: ' . $client->name);
        $account->loadLocalizationSettings($client);
        Auth::loginUsingId($this->recurInvoice->activeUser()->id);
        $asttpPeriod = $this->recurInvoice->getAsttpPeriod();
        try {
            echo date('r'), ' Import cdrs from astpp DB', PHP_EOL;
            Utils::logAstppService('info', 'Import cdrs from astpp DB');         
            $astppService->importCdrs($client, $asttpPeriod['start'], $asttpPeriod['end']);
            echo date('r'), ' Rate Client Calls', PHP_EOL;
            Utils::logAstppService('info', 'Rate Client Calls');
            $astppService->rateClientCalls($client);
            echo date('r'), ' Calls were rated', PHP_EOL;
            Utils::logAstppService('info', 'Calls were rated');

            $logService->logAstppRateNotFound($client->id, $asttpPeriod['start'], $asttpPeriod['end']);

            echo date('r'), ' Bill Client by recurring invoice', PHP_EOL;
            Utils::logAstppService('info', 'Bill Client by recurring invoice');
            $invoice = $astppService->billClient($this->recurInvoice);
            if ($invoice) {
                Utils::logAstppService('info', 'Created invoice ' . $invoice->invoice_number . ' on sum ' . $invoice->amount);
            }
            if ($invoice && ! $invoice->isPaid() && $account->auto_email_invoice) {
                echo date('r') . ' Not billed - Sending Invoice', PHP_EOL;
                dispatch(new SendInvoiceEmail($invoice, $invoice->user_id));
                Utils::logAstppService('info', 'Sending Invoice to client');
            } elseif ($invoice) {
                echo date('r') . ' Successfully billed invoice', PHP_EOL;
                Utils::logAstppService('info', 'Successfully billed invoice');
            }           
        } catch (\Exception $e) {
            echo 'ERROR:' . $e->getMessage() . PHP_EOL;
            echo 'File: ' . $e->getFile() . PHP_EOL;
            echo 'Line: ' . $e->getLine() . PHP_EOL;
            echo 'Trace: ' . $e->getTraceAsString() . PHP_EOL;            
            Utils::logError($e);
            Utils::logAstppService('info', 'Error invoice process: ', $e->getMessage());
        }
        Auth::logout();
    }
}
