<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Ninja\Repositories\CdrRepository;

use Auth;
use Exception;

class AttachCdrReportToInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telco:attach-cdr-report-to-invoice {invoiceNum : The Number of invoice}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Attach CDR Destination report to invoice';

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
    public function handle(CdrRepository $cdrRepository)
    {
        $invoiceNum = $this->argument('invoiceNum');
        $invoice = \App\Models\Invoice::where('invoice_number', '=', $invoiceNum)->first();
        if (!$invoice) {
            $this->error('Invoice number ' . $invoiceNum . ' is not found');
            return false;
        } 
        if (!$this->confirm('Do you wish to generate CDR destination report and bind it to invoice ' . $invoiceNum .' ?')) {
            return true;
        }
        try {
            Auth::loginUsingId($invoice->activeUser()->id);
            $cdrRepository->attachDestinationReportToInvoice($invoice);
        } catch (Exception $exception) {
            $this->info(date('r') . ' Error: ' . $exception->getMessage());
        } 
    }
}
