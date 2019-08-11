<?php
namespace Modules\ImportColt\Services;

use Utils;
use Carbon;

use Illuminate\Support\Facades\Storage;

use App\Ninja\Repositories\ClientRepository;
use App\Ninja\Repositories\CdrRepository;
use Modules\ImportColt\Repositories\ImportColtRepository;

use App\Services\RateMachineService;
use App\Services\InvoiceService;

class ColtService
{
    protected $clientRepository;
    protected $cdrRepository;
    protected $rateMachineService;
    protected $importColtRepository;
    protected $invoiceService;

    public function __construct(ClientRepository $clientRepository, 
        CdrRepository $cdrRepository,
        ImportColtRepository $importcoltRepo,
        RateMachineService $rateMachineService,
        InvoiceService $invoiceService) {
        $this->clientRepository = $clientRepository;
        $this->cdrRepository = $cdrRepository;
        $this->rateMachineService = $rateMachineService;
        $this->importColtRepository = $importcoltRepo;
        $this->invoiceService = $invoiceService;
    }

    public function parseColtFile($fileName, $isVerify = false) {
        $coltContents = Storage::get($fileName);
        $coltData = [];
        foreach(preg_split( '/\r\n|\r|\n/', $coltContents) as $index => $row) {
            $data = str_getcsv($row, ';');
            if (count($data) > 17) {
                $did = $this->normalizeDid($data[4]);
                $datetime = Carbon::parse($data[5] . ' ' . $data[6]);
                $dst = $this->normalizeDst($data[9]);
                $dur = $data[10];
                $coltData[] = [
                    'did' => $did,
                    'datetime' => Utils::toSqlDate($datetime),
                    'dst' => $dst,
                    'dur' => $dur,
                    'cost' => 0,
                ];
            }
            if ($isVerify && $index === 2) {
                break;
            }
        }
        return $coltData;
    }

    public function buildCdr(array $data, int $import_colt_id) {
        foreach ($data as $item) {
            $item['import_colt_id'] = $import_colt_id;
            $this->cdrRepository->save($item);
        }
        $this->cdrRepository->updateClient();
    }

    public function rateColtCalls($importColtId) {
       $clients = \App\Models\Client::scope()
        ->whereHas('cdrs', function($query) use ($importColtId) {
            $query->where('import_colt_id', $importColtId)
                ->where('done', 0);
        })
        ->orderBy('id')
        ->get();
        foreach ($clients as $client) {
            $this->rateClientCalls($client, $importColtId);
        }
    }

    public function rateClientCalls(\App\Models\Client $client, int $importColtId)
    {
        $cdrs = \App\Models\Cdr::scope()
            ->where('import_colt_id', $importColtId)
            ->where('client_id', $client->id)
            ->where('done', 0)
            ->orderBy('id')
            ->get();
        
        $this->rateMachineService->initMachine($client);
        foreach ($cdrs as $cdr) {
            $cdr = $this->rateMachineService->calculateCall($cdr);
            $cdr->save();
        }
    }

    /**
     * Bill clients by cdr of colt file
     */
    public function billCdr($importColtId) {
        $clients = \App\Models\Client::scope()
        ->whereHas('cdrs', function($query) use ($importColtId) {
            $query->where('import_colt_id', $importColtId)
                ->where('done', 1);
        })
        ->orderBy('id')
        ->get();
        foreach ($clients as $client) {
            $this->billClientCalls($client, $importColtId);
        }
    }

    public function billClientCalls($client, $importColtId) {
        $sumCdr = \App\Models\Cdr::scope()
            ->selectRaw('sum(cost) as sum_cost, min(datetime) as date_from, max(datetime) as date_to')
            ->where('import_colt_id', $importColtId)
            ->where('client_id', $client->id)
            ->where('done', 1)
            ->whereNull('invoice_id')
            ->first();
        $coltInvoice = \App\Models\Invoice::scope()
            ->with('invoice_items')
            ->where('client_id', $client->id)
            ->where('invoice_category_id', INVOICE_ITEM_CATEGORY_COLT)
            ->first();
        if (!$coltInvoice) {
            return true;
        }
        $totalSum = 0;
        $hasTelcoRates = false;
        foreach ($coltInvoice->invoice_items as $item) {
            if ($item->product_type === 'telcorates') {
                $hasTelcoRates = true;
            } else {
                $totalSum += round($item->cost * $item->qty, 2);
            }
        }
        if ($hasTelcoRates) {
            $totalSum += $sumCdr->sum_cost;
        }
        // If we don't exceed the limit we don't create invoice
        if ($client->invoice_sum_limit >= $totalSum) {
            return true;
        }

        $account = \Auth::user()->account;        
        $invoice = $account->createInvoice(ENTITY_INVOICE, $client->id);
        $invoice->public_id = 0;
        $importColt = $this->importColtRepository->getById($importColtId);
        $invoice->invoice_date = $importColt->invoice_date;
        if (!empty($coltInvoice->due_date)) {
            $invoice->due_date = Utils::toSqlDate(
                Carbon::parse($importColt->invoice_date)
                ->addDay(Carbon::parse($coltInvoice->due_date)->day)
            );
        }
        $invoice->invoice_category_id = INVOICE_ITEM_CATEGORY_ORDINARY;
        $invoice->invoice_items = collect([]);
        $sumCost = $sumCdr->sum_cost;

        $billing_period_start = Carbon::parse($sumCdr->date_from)
            ->startOfMonth()
            ->toDateString();
        $billing_period_stop = Carbon::parse($sumCdr->date_to)
            ->endOfMonth()
            ->toDateString();
        foreach ($coltInvoice->invoice_items as $item) {
            $invoice_item = $item->toArray();
            $invoice_item['invoice_id'] = null;
            $invoice_item['notes'] = str_replace(
                array('$billing_period_start', '$billing_period_stop'),
                array($billing_period_start, $billing_period_stop),
                $invoice_item['notes']
            );
            if ($invoice_item['product_type'] === 'telcorates') {
                $invoice_item['qty'] = 1;
                $invoice_item['cost'] = $sumCost;
                $sumCost = 0;
            }
            if ($invoice_item['cost'] > 0) {
                $invoice->invoice_items->push($invoice_item);
            }
        }
        $data = $invoice->toArray();
        $invoice = $this->invoiceService->save($data);
    }

    private function normalizeDid($did) {
        return preg_replace('/^00/', '', $did);
    }

    private function normalizeDst($dst) {
        // Domestic in Austria
        if (preg_match('/^0[1-9]\d+/', $dst)) {
            return preg_replace('/^0([1-9])(\d+)/', '41${1}${2}', $dst);
        }
        // Local
        if (preg_match('/^[1-9]\d+/', $dst)) {
            return preg_replace('/^([1-9])(\d+)/', '431${1}${2}', $dst);
        }
        // International
        if (preg_match('/^00[1-9]\d+/', $dst)) {
            return preg_replace('/^00([1-9])(\d+)/', '${1}${2}', $dst);
        }
        if (preg_match('/^\+[1-9]\d+/', $dst)) {
            return preg_replace('/^\+([1-9])(\d+)/', '${1}${2}', $dst);
        }
    }
}