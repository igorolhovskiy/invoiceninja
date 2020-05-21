<?php

namespace App\Services;

use Exception;

use Utils;

use App\Events\QuoteItemsWereCreated;
use App\Events\QuoteItemsWereUpdated;
use App\Events\InvoiceItemsWereCreated;
use App\Events\InvoiceItemsWereUpdated;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Invitation;
use App\Ninja\Repositories\Astpp\AccountRepository as AstppAccountRepository;
use App\Ninja\Repositories\Astpp\CdrRepository as AstppCdrRepository;
use App\Ninja\Repositories\CdrRepository;
use App\Ninja\Repositories\InvoiceRepository;

use App\Services\RateMachineService;
use App\Services\PaymentService;

/**
 * Class AstppService.
 */
class AstppService
{

  protected $astppAccountRepo;
  protected $astppCdrRepo;
  protected $cdrRepo;
  protected $rateMachineService;

  public function __construct(
    AstppAccountRepository $astppAccountRepo,
    AstppCdrRepository $astppCdrRepo,
    CdrRepository $cdrRepo,
    InvoiceRepository $invoiceRepo,
    RateMachineService $rateMachineService,
    PaymentService $paymentService
    ) {
    $this->astppAccountRepo = $astppAccountRepo;
    $this->astppCdrRepo = $astppCdrRepo;
    $this->cdrRepo = $cdrRepo;
    $this->invoiceRepo = $invoiceRepo;
    $this->rateMachineService = $rateMachineService;
    $this->paymentService = $paymentService;
  }

  public function importCdrs($client, $startDate, $endDate) {
    if (!$client || !$client->astpp_client_id) {
      throw new Exception('Not found Astpp account');
    }
    echo "Get Data from ASTPP DB for number: $client->astpp_client_id", PHP_EOL;
    Utils::logAstppService('info', "Get Data from ASTPP DB for number: $client->astpp_client_id");
    $astppAccount = $this->astppAccountRepo->getAccountByNumber($client->astpp_client_id);
    if (!$astppAccount) {
      throw new Exception('Not found Astpp account in Astpp DB: ' . $client->astpp_client_id);
    }
    $astppCdr = $this->astppCdrRepo->getCdr($astppAccount->id, $startDate, $endDate);
    echo "There is found " . $astppCdr->count() . ' cdr records in astpp DB', PHP_EOL;
    Utils::logAstppService('info', "There is found " . $astppCdr->count() . ' cdr records in astpp DB');
    $counter = 0;
    foreach($astppCdr as $cdr) {
      if ($this->cdrRepo->findByAstppId($cdr->uniqueid)) {
        continue;
      }
      $this->cdrRepo->save([
        'client_id' => $client->id,
        'astpp_cdr_uniqueid' => $cdr->uniqueid,
        'did' => $cdr->callerid,
        'datetime' => $cdr->callstart,
        'dst' => $cdr->translated_dst,
        'dur' => $cdr->billseconds
      ]);
      $counter += 1;
    }
    echo date('r'), " $counter cdr records were added", PHP_EOL;
    Utils::logAstppService('info', "$counter cdr records were added");
  }

  public function rateClientCalls(\App\Models\Client $client)
  {
    $cdrs = \App\Models\Cdr::scope()
        ->whereNotNull('astpp_cdr_uniqueid')
        ->where('client_id', $client->id)
        ->where('done', 0)
        ->orderBy('id')
        ->get();
    
    if (!$this->rateMachineService->initMachine($client, INVOICE_ITEM_CATEGORY_ASTPP)) {
      Utils::logAstppService('error', 'Init rate machine return False for client ' . $client->id);
    }
    foreach ($cdrs as $cdr) {
        $cdr = $this->rateMachineService->calculateCall($cdr);
        $cdr->save();
    }
    $this->rateMachineService->resetMachine();
  }

  public function billClient($recurInvoice)
  {
    $client = $recurInvoice->client;
    $sumCdr = \App\Models\Cdr::scope()
      ->selectRaw('sum(cost) as sum_cost, min(datetime) as date_from, max(datetime) as date_to')
      ->whereNotNull('astpp_cdr_uniqueid')
      ->where('client_id', $client->id)
      ->where('done', 1)
      ->whereNull('invoice_id')
      ->first();

    $totalSum = $recurInvoice->invoice_items->sum(function($item) {
      return $item->product_type !== 'telcorates'
        ? round($item->cost * $item->qty, 2) : 0;
    });
    $telcoratesItem = $recurInvoice->invoice_items->first(function($item) {
      return $item->product_type === 'telcorates';
    });
    if ($telcoratesItem) {
      $totalSum += $sumCdr->sum_cost ?? 0;
      $telcoratesItem->cost = $sumCdr->sum_cost ?? 0;
      $telcoratesItem->qty = 1;
    }

    // If we don't exceed the limit we don't create invoice
    if ($client->invoice_sum_limit >= $totalSum) {
      echo "Total sum less sum limit, invoice don't bill", PHP_EOL;
      Utils::logAstppService('info', "Total sum $totalSum less sum limit, invoice don't bill");
      return false;
    }

    $asttpPeriod = $recurInvoice->getAsttpPeriod();    
    foreach ($recurInvoice->invoice_items as $item) {
      $item->notes = str_replace(
          array('$billing_period_start', '$billing_period_stop'),
          array($asttpPeriod['start'], $asttpPeriod['end']),
          $item->notes
      );
    }
    $invoice = $this->createInvoice($recurInvoice, $totalSum);
    if ($invoice) {
      \App\Models\Cdr::scope()
      ->whereNotNull('astpp_cdr_uniqueid')
      ->where('client_id', $client->id)
      ->where('done', 1)
      ->whereNull('invoice_id')
      ->update(['invoice_id' => $invoice->id]);      
    }
    return $invoice;
  }

  private function createInvoice($recurInvoice, $totalSum)
  {
    $recurInvoice->load('account.timezone', 'client', 'user');
    $client = $recurInvoice->client;

    if ($client->deleted_at) {
        return false;
    }

    if (! $recurInvoice->user->confirmed) {
        return false;
    }

    if (! $recurInvoice->shouldSendToday()) {
        return false;
    }
    $invoice = Invoice::createNew($recurInvoice);
    $invoice->is_public = true;
    $invoice->invoice_type_id = INVOICE_TYPE_STANDARD;
    $invoice->client_id = $recurInvoice->client_id;
    $invoice->recurring_invoice_id = $recurInvoice->id;
    $invoice->invoice_number = $recurInvoice->account->getNextNumber($invoice);
    $invoice->amount = $totalSum;
    $invoice->balance = $totalSum;
    $invoice->invoice_date = date_create()->format('Y-m-d');
    $invoice->discount = $recurInvoice->discount;
    $invoice->po_number = $recurInvoice->po_number;
    $invoice->public_notes = Utils::processVariables($recurInvoice->public_notes, $client);
    $invoice->terms = Utils::processVariables($recurInvoice->terms ?: $recurInvoice->account->invoice_terms, $client);
    $invoice->invoice_footer = Utils::processVariables($recurInvoice->invoice_footer ?: $recurInvoice->account->invoice_footer, $client);
    $invoice->tax_name1 = $recurInvoice->tax_name1;
    $invoice->tax_rate1 = $recurInvoice->tax_rate1;
    $invoice->tax_name2 = $recurInvoice->tax_name2;
    $invoice->tax_rate2 = $recurInvoice->tax_rate2;
    $invoice->invoice_design_id = $recurInvoice->invoice_design_id;
    $invoice->custom_value1 = $recurInvoice->custom_value1 ?: 0;
    $invoice->custom_value2 = $recurInvoice->custom_value2 ?: 0;
    $invoice->custom_taxes1 = $recurInvoice->custom_taxes1 ?: 0;
    $invoice->custom_taxes2 = $recurInvoice->custom_taxes2 ?: 0;
    $invoice->custom_text_value1 = Utils::processVariables($recurInvoice->custom_text_value1, $client);
    $invoice->custom_text_value2 = Utils::processVariables($recurInvoice->custom_text_value2, $client);
    $invoice->is_amount_discount = $recurInvoice->is_amount_discount;
    $invoice->due_date = $recurInvoice->getDueDate();
    $invoice->save();

    foreach ($recurInvoice->invoice_items as $recurItem) {
        $item = InvoiceItem::createNew($recurItem);
        $item->product_id = $recurItem->product_id;
        $item->qty = $recurItem->qty;
        $item->cost = $recurItem->cost;
        $item->notes = Utils::processVariables($recurItem->notes, $client);
        $item->product_key = Utils::processVariables($recurItem->product_key, $client);
        $item->tax_name1 = $recurItem->tax_name1;
        $item->tax_rate1 = $recurItem->tax_rate1;
        $item->tax_name2 = $recurItem->tax_name2;
        $item->tax_rate2 = $recurItem->tax_rate2;
        $item->custom_value1 = Utils::processVariables($recurItem->custom_value1, $client);
        $item->custom_value2 = Utils::processVariables($recurItem->custom_value2, $client);
        $item->discount = $recurItem->discount;
        $invoice->invoice_items()->save($item);
    }

    foreach ($recurInvoice->documents as $recurDocument) {
        $document = $recurDocument->cloneDocument();
        $invoice->documents()->save($document);
    }

    foreach ($recurInvoice->invitations as $recurInvitation) {
        $invitation = Invitation::createNew($recurInvitation);
        $invitation->contact_id = $recurInvitation->contact_id;
        $invitation->invitation_key = strtolower(str_random(RANDOM_KEY_LENGTH));
        $invoice->invitations()->save($invitation);
    }

    $recurInvoice->last_sent_date = date('Y-m-d');
    $recurInvoice->save();

    if ($recurInvoice->getAutoBillEnabled() && ! $recurInvoice->account->auto_bill_on_due_date) {
        // autoBillInvoice will check for ACH, so we're not checking here
        if ($this->paymentService->autoBillInvoice($invoice)) {
            // update the invoice reference to match its actual state
            // this is to ensure a 'payment received' email is sent
            $invoice->invoice_status_id = INVOICE_STATUS_PAID;
        }
    }

    $this->cdrRepo->attachCdrToInvoice($invoice);

    $this->cdrRepo->attachDestinationReportToInvoice($invoice);

    $this->dispatchEvents($invoice);

    return $invoice;
  }

  private function dispatchEvents($invoice)
  {
      if ($invoice->isType(INVOICE_TYPE_QUOTE)) {
          if ($invoice->wasRecentlyCreated) {
              event(new QuoteItemsWereCreated($invoice));
          } else {
              event(new QuoteItemsWereUpdated($invoice));
          }
      } else {
          if ($invoice->wasRecentlyCreated) {
              event(new InvoiceItemsWereCreated($invoice));
          } else {
              event(new InvoiceItemsWereUpdated($invoice));
          }
      }
  }  
}