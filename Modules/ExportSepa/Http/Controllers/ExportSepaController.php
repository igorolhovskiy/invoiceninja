<?php

namespace Modules\ExportSepa\Http\Controllers;

use Auth;
use DB;
use App\Http\Controllers\BaseController;
use App\Services\DatatableService;
use App\Ninja\Repositories\InvoiceRepository;
use Modules\ExportSepa\Datatables\ExportSepaDatatable;
use Modules\ExportSepa\Repositories\ExportSepaRepository;
use Modules\ExportSepa\Http\Requests\ExportSepaRequest;
use Modules\ExportSepa\Http\Requests\CreateExportSepaRequest;
use Modules\ExportSepa\Http\Requests\UpdateExportSepaRequest;
use Modules\ExportSepa\Models\ExportSepa;

use Modules\ExportSepa\Services\ExportSepaService;

class ExportSepaController extends BaseController
{
    protected $exportsepaRepo;
    protected $invoiceRepo;
    protected $exportSepaService;
    //protected $entityType = 'exportsepa';

    public function __construct(ExportSepaRepository $exportsepaRepo,
        InvoiceRepository $invoiceRepo,
        ExportSepaService $exportSepaService)
    {
        //parent::__construct();

        $this->exportsepaRepo = $exportsepaRepo;
        $this->invoiceRepo = $invoiceRepo;
        $this->exportSepaService = $exportSepaService;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('list_wrapper', [
            'entityType' => 'exportsepa',
            'datatable' => new ExportSepaDatatable(),
            'title' => mtrans('exportsepa', 'exportsepa_list'),
        ]);
    }

    public function datatable(DatatableService $datatableService)
    {
        $search = request()->input('sSearch');
        $userId = Auth::user()->filterId();

        $datatable = new ExportSepaDatatable();
        $query = $this->exportsepaRepo->find($search, $userId);

        return $datatableService->createDatatable($datatable, $query);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(ExportSepaRequest $request)
    {
        $data = [
            'exportsepa' => null,
            'method' => 'POST',
            'url' => 'exportsepa',
            'title' => mtrans('exportsepa', 'new_exportsepa'),
            'invoices' => $this->getInvoices()
        ];

        return view('exportsepa::edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(CreateExportSepaRequest $request)
    {
        $exportsepa = $this->exportsepaRepo->save($request->input());
        $request->session()->flash('http_action', '/exportsepa/' . $exportsepa->id . '/generate-sepa');
        return redirect()->to('exportsepa')
            ->with('message', mtrans('exportsepa', 'created_exportsepa'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(ExportSepaRequest $request)
    {
        return redirect()->to("exportsepa/{$request->exportsepa}");
    }

    /**
     * Show the form for editing a resource.
     * @return Response
     */
    public function show(ExportSepaRequest $request)
    {
        $exportsepa = $request->entity();

        $data = [
            'exportsepa' => $exportsepa,
            'method' => 'GET',
            'url' => 'exportsepa',
            'title' => mtrans('exportsepa', 'view_exportsepa'),
            'invoices' => $this->getExportInvoices($exportsepa)
        ];

        return view('exportsepa::edit', $data);
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UpdateExportSepaRequest $request)
    {
        $exportsepa = $this->exportsepaRepo->save($request->input(), $request->entity());

        return redirect()->to($exportsepa->present()->editUrl)
            ->with('message', mtrans('exportsepa', 'updated_exportsepa'));
    }

    /**
     * Update multiple resources
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('public_id') ?: request()->input('ids');
        $count = $this->exportsepaRepo->bulk($ids, $action);

        return redirect()->to('exportsepa')
            ->with('message', mtrans('exportsepa', $action . '_exportsepa_complete'));
    }

    public function generateSepaXml(ExportSepaRequest $request)
    {
        $exportsepa = $request->entity();
        $sepaData = $this->exportSepaService->getSepaData($exportsepa);
        return response(view('exportsepa::sepa-xml', $sepaData), 200, [
            'Content-Type' => 'text/xml',
            'Content-Disposition' => 'attachment; filename="sepa.xml"',
        ]);                
    }

    protected function getInvoices()
    {
        $accountId = Auth::user()->account_id;  
        return $this->invoiceRepo
            ->getInvoices($accountId)
            ->where('invoices.invoice_type_id', '=', INVOICE_TYPE_STANDARD)
            ->whereNotIn('invoice_status_id', [INVOICE_STATUS_DRAFT, INVOICE_STATUS_PAID])
            ->orderBy('invoices.invoice_number')
            ->get();

    }

    protected function getExportInvoices($exportsepa)
    {
        $accountId = Auth::user()->account_id;  
        return $this->invoiceRepo
            ->getInvoices($accountId)
            ->whereExists(function ($query) use ($exportsepa) {
                $query->select(DB::raw(1))
                      ->from('exportsepa_items')
                      ->whereRaw('exportsepa_items.invoice_id = invoices.id')
                      ->where('exportsepa_items.exportsepa_id', $exportsepa->id);
            })
            ->orderBy('invoices.invoice_number')
            ->get();

    }
}
