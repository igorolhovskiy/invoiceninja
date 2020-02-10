<?php
namespace Modules\ImportColt\Services;

use Utils;
use Carbon;

use Illuminate\Support\Facades\Storage;

use App\Ninja\Repositories\ClientRepository;
use App\Ninja\Repositories\CdrRepository;
use Modules\ImportColt\Repositories\ImportColtRepository;
use App\Ninja\Repositories\InvoiceRepository;

use App\Services\RateMachineService;

class ColtService
{
    protected $clientRepository;
    protected $cdrRepository;
    protected $rateMachineService;
    protected $importColtRepository;
    protected $invoiceRepo;

    private $srcPatterns;
    private $dstPatterns;

    public function __construct(ClientRepository $clientRepository,
        CdrRepository $cdrRepository,
        ImportColtRepository $importcoltRepo,
        RateMachineService $rateMachineService,
        InvoiceRepository $invoiceRepo) {

        $this->clientRepository = $clientRepository;
        $this->cdrRepository = $cdrRepository;
        $this->rateMachineService = $rateMachineService;
        $this->importColtRepository = $importcoltRepo;
        $this->invoiceRepo = $invoiceRepo;

        $this->dstPatterns = array(
            array('^0([1-9])(\d+)', '43$1$2'), // Austria National
            array('^00([1-9])(\d+)', '$1$2'), // International strip 00
            array('^([1-9])(\d+)', '431$1$2'), // Austria Vienna Domestic -- ?
            array('^\+([1-9])(\d+)', '$1$2'), // International strip +
        );

        $this->srcPatterns = array(
            array('^00([1-9])(\d+)', '$1$2'), // International strip 00
            array('^\+([1-9])(\d+)', '$1$2'), // International strip +
            array('^(?!00)(\d+)', '$1'),      // Just remove all non-digits from number
        );
        
    }

    public function parseColtFile($fileName, $isVerify = false) {
        $coltContents = Storage::get($fileName);
        $coltData = [];
        foreach(preg_split( '/\r\n|\r|\n/', $coltContents) as $index => $row) {
            $data = str_getcsv($row, ';');
            if (count($data) > 17) {
                $did = $this->normalizeSrc($data[4]);
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
        echo "All data was saved to cdrs table" . PHP_EOL;
        $update_result = $this->cdrRepository->updateClient();
        return $update_result;
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
        return $clients->count();
    }

    public function rateClientCalls(\App\Models\Client $client, int $importColtId)
    {
        $cdrs = \App\Models\Cdr::scope()
            ->where('import_colt_id', $importColtId)
            ->where('client_id', $client->id)
            ->where('done', 0)
            ->orderBy('id')
            ->get();
        
        if (!$this->rateMachineService->initMachine($client)) {
            Utils::logColtService('error', 'Init rate machine return False for client ' . $client->id);
        }
        foreach ($cdrs as $cdr) {
            $cdr = $this->rateMachineService->calculateCall($cdr);
            $cdr->save();
        }
        $this->rateMachineService->resetMachine();
    }

    /**
     * Bill clients by cdr of colt file
     */
    public function billCdr($importColtId, $isNeedLog = false) {
        $clients = \App\Models\Client::scope()
        ->whereHas('cdrs', function($query) use ($importColtId) {
            $query->where('import_colt_id', $importColtId)
                ->where('done', 1);
        })
        ->orderBy('id')
        ->get();
        $stat = [
            'count' => 0,
            'sum' => 0
        ];
        foreach ($clients as $client) {
            $invoice = $this->billClientCalls($client, $importColtId);
            if ($invoice) {
                $stat['count'] += 1;
                $stat['sum'] += $invoice->amount;
                Utils::logColtService('info', "Created Invoice for client {$client->name} on sum :{$invoice->amount}");
            }
        }
        return $stat;
    }

    public function billClientCalls($client, $importColtId) {
        $sumCdr = \App\Models\Cdr::scope()
            ->selectRaw('sum(cost) as sum_cost, min(datetime) as date_from, max(datetime) as date_to')
            ->whereNotNull('import_colt_id')
            ->where('client_id', $client->id)
            ->where('done', 1)
            ->whereNull('invoice_id')
            ->first();
        $coltInvoice = \App\Models\Invoice::scope()
            ->with('account.timezone', 'invoice_items', 'client', 'user')
            ->where('client_id', $client->id)
            ->where('invoice_category_id', INVOICE_ITEM_CATEGORY_COLT)
            ->first();
        if (!$coltInvoice) {
            return false;
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
            return false;
        }

        $account = \Auth::user()->account;        
        $invoice = $account->createInvoice(ENTITY_INVOICE, $client->id);

        $invoice->public_id = 0;
        $invoice->import_colt_id = $importColtId;

        $importColt = $this->importColtRepository->getById($importColtId);
        $invoice->invoice_date = $importColt->invoice_date;
        
        $invoice->discount = $coltInvoice->discount;
        $invoice->po_number = $coltInvoice->po_number;
        $invoice->public_notes = Utils::processVariables($coltInvoice->public_notes, $client);
        $invoice->terms = Utils::processVariables($coltInvoice->terms ?: $coltInvoice->account->invoice_terms, $client);
        $invoice->invoice_footer = Utils::processVariables($coltInvoice->invoice_footer ?: $coltInvoice->account->invoice_footer, $client);
        $invoice->tax_name1 = $coltInvoice->tax_name1;
        $invoice->tax_rate1 = $coltInvoice->tax_rate1;
        $invoice->tax_name2 = $coltInvoice->tax_name2;
        $invoice->tax_rate2 = $coltInvoice->tax_rate2;
        $invoice->invoice_design_id = $coltInvoice->invoice_design_id;
        $invoice->custom_value1 = $coltInvoice->custom_value1 ?: 0;
        $invoice->custom_value2 = $coltInvoice->custom_value2 ?: 0;
        $invoice->custom_taxes1 = $coltInvoice->custom_taxes1 ?: 0;
        $invoice->custom_taxes2 = $coltInvoice->custom_taxes2 ?: 0;
        $invoice->custom_text_value1 = Utils::processVariables($coltInvoice->custom_text_value1, $client);
        $invoice->custom_text_value2 = Utils::processVariables($coltInvoice->custom_text_value2, $client);
        $invoice->is_amount_discount = $coltInvoice->is_amount_discount;

        $coltInvoice->due_date = $this->getDueDate($coltInvoice);

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

        $invoice = $this->invoiceRepo->save($data);

        \App\Models\Cdr::scope()
            ->whereNotNull('import_colt_id')
            ->where('client_id', $client->id)
            ->where('done', 1)
            ->whereNull('invoice_id')
            ->update(['invoice_id' => $invoice->id]);

        $this->cdrRepository->attachCdrToInvoice($invoice);

        return $invoice;
    }

    private function getDueDate($invoice) {
        $now = time();
        if ($invoice->client->payment_terms != 0) {
            // No custom due date set for this invoice; use the client's payment terms
            $days = $invoice->client->defaultDaysDue();

            return date('Y-m-d', strtotime('+'.$days.' day', $now));
        } elseif ($invoice->account->payment_terms != 0) {
            $days = $invoice->account->defaultDaysDue();

            return date('Y-m-d', strtotime('+'.$days.' day', $now));
        }
        return null;      
    }

    private function normalizeNumber($number, $patterns) {
        foreach ($patterns as $pattern) {
            if (preg_match('/' . $pattern[0] . '/', $number, $matches)) {
                $return_number = $pattern[1];
                foreach (array_slice($matches, 1) as $matchKey=>$matchValue) {
                    $return_number = str_replace('$' . ($matchKey + 1), $matchValue, $return_number);
                }
                return $return_number;
            }
        }
        return $number;
    }

    private function normalizeDst($dst) {
        return $this->normalizeNumber($dst, $this->dstPatterns);
    }

    private function normalizeSrc($src) {
        return $this->normalizeNumber($src, $this->srcPatterns);
    }
}