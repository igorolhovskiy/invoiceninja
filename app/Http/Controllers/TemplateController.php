<?php
namespace App\Http\Controllers;

use App\Ninja\Datatables\InvoiceDatatable;
use App\Services\InvoiceService;
use App\Http\Requests\InvoiceRequest;
use Auth;
use Cache;
use Input;
use Session;
use Utils;
use View;
use App\Models\Account;
use App\Models\Client;
use App\Models\InvoiceDesign;
use App\Models\Product;
use App\Models\TaxRate;
use App\Models\Invoice;

class TemplateController extends BaseController
{

  protected $invoiceService;
  protected $entityType = ENTITY_INVOICE;

  public function __construct(InvoiceService $invoiceService)
  {
      $this->invoiceService = $invoiceService;
  }
  
  public function index()
  {
      $datatable = new InvoiceDatatable();
      $datatable->entityType = ENTITY_TEMPLATE;

      $data = [
        'title' => trans('texts.templates'),
        'entityType' => ENTITY_TEMPLATE,
        'datatable' => $datatable,
      ];

      return response()->view('list_wrapper', $data);
  }

  public function getDatatable($clientPublicId = null)
  {
      $accountId = Auth::user()->account_id;
      $search = Input::get('sSearch');

      return $this->invoiceService->getDatatable($accountId, $clientPublicId, ENTITY_TEMPLATE, $search);
  }

  public function bulk()
  {
      $action = Input::get('bulk_action') ?: Input::get('action');

      $ids = Input::get('bulk_public_id') ?: (Input::get('public_id') ?: Input::get('ids'));

      $count = $this->invoiceService->bulk($ids, $action);

      if ($count > 0) {
          $key = "{$action}d_template";
          $message = Utils::pluralize($key, $count);
          Session::flash('message', $message);
      }

      return $this->returnBulk(ENTITY_TEMPLATE, $action, $ids);
  }

  public function create(InvoiceRequest $request, $clientPublicId = 0)
  {
      $account = Auth::user()->account;
      $clientId = null;
      if ($clientPublicId) {
          $clientId = Client::getPrivateId($clientPublicId);
      }
      $invoice = $account->createInvoice(ENTITY_INVOICE, $clientId);
      $invoice->public_id = 0;
      $invoice->invoice_category_id = INVOICE_ITEM_CATEGORY_COLT;

      $data = [
          'entityType' => $invoice->getEntityType(),
          'invoice' => $invoice,
          'data' => Input::old('data'),
          'method' => 'POST',
          'url' => 'invoices',
          'title' => trans('texts.new_quote'),
      ];
      $data = array_merge($data, self::getViewModel());

      return View::make('invoices.edit', $data);
  }

  private static function getViewModel()
  {
      $account = Auth::user()->account;

      $invoiceCategories = [
        INVOICE_ITEM_CATEGORY_COLT => Invoice::$invoiceCategories[INVOICE_ITEM_CATEGORY_COLT]
      ];

      return [
        'account' => Auth::user()->account->load('country'),
        'products' => Product::scope()->orderBy('product_key')->get(),
        'taxRateOptions' => $account->present()->taxRateOptions,
        'clients' => Client::scope()->with('contacts', 'country')->orderBy('name')->get(),
        'taxRates' => TaxRate::scope()->orderBy('name')->get(),
        'sizes' => Cache::get('sizes'),
        'paymentTerms' => Cache::get('paymentTerms'),
        'invoiceDesigns' => InvoiceDesign::getDesigns(),
        'invoiceFonts' => Cache::get('fonts'),
        'invoiceLabels' => Auth::user()->account->getInvoiceLabels(),
        'isRecurring' => false,
        'expenses' => collect(),
        'invoiceCategories' => $invoiceCategories          
      ];
  }
}