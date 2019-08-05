<?php

namespace Modules\ImportColt\Http\Controllers;

use Auth;
use App\Http\Controllers\BaseController;
use App\Services\DatatableService;
use Modules\ImportColt\Datatables\ImportColtDatatable;
use Modules\ImportColt\Repositories\ImportColtRepository;
use Modules\ImportColt\Http\Requests\ImportColtRequest;
use Modules\ImportColt\Http\Requests\CreateImportColtRequest;
use Modules\ImportColt\Http\Requests\UpdateImportColtRequest;
use Modules\ImportColt\Http\Requests\ParceColtRequest;
use App\Ninja\Repositories\ClientRepository;

use Modules\ImportColt\Services\ColtService;

use Modules\ImportColt\Jobs\ParseColt;

class ImportColtController extends BaseController
{
    protected $ImportColtRepo;
    //protected $entityType = 'importcolt';

    protected $coltService;

    protected $clientRepository;

    public function __construct(ImportColtRepository $importcoltRepo,
        ColtService $coltService,
        ClientRepository $clientRepository)
    {
        //parent::__construct();

        $this->importcoltRepo = $importcoltRepo;
        $this->coltService = $coltService;
        $this->clientRepository = $clientRepository;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('list_wrapper', [
            'entityType' => 'importcolt',
            'datatable' => new ImportColtDatatable(),
            'title' => mtrans('importcolt', 'importcolt_list'),
        ]);
    }

    public function datatable(DatatableService $datatableService)
    {
        $search = request()->input('sSearch');
        $userId = Auth::user()->filterId();

        $datatable = new ImportColtDatatable();
        $query = $this->importcoltRepo->find($search, $userId);

        return $datatableService->createDatatable($datatable, $query);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(ImportColtRequest $request)
    {
        $data = [
            'importcolt' => null,
            'method' => 'POST',
            'url' => 'importcolt',
            'title' => mtrans('importcolt', 'new_importcolt'),
        ];

        return view('importcolt::edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(CreateImportColtRequest $request)
    {
        $importColt = $this->importcoltRepo->save($request->input());
        
        $job = new ParseColt(\Auth::user(), $importColt);
        dispatch($job);

        return redirect()->to('importcolt')
            ->with('message', mtrans('importcolt', 'created_importcolt'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(ImportColtRequest $request)
    {
        $importcolt = $request->entity();

        $data = [
            'importcolt' => $importcolt,
            'method' => 'PUT',
            'url' => 'importcolt/' . $importcolt->public_id,
            'title' => mtrans('importcolt', 'edit_importcolt'),
        ];

        return view('importcolt::edit', $data);
    }

    /**
     * Show the form for editing a resource.
     * @return Response
     */
    public function show(ImportColtRequest $request)
    {
        return redirect()->to("importcolt/{$request->importcolt}/edit");
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UpdateImportColtRequest $request)
    {
        $importcolt = $this->importcoltRepo->save($request->input(), $request->entity());

        return redirect()->to($importcolt->present()->editUrl)
            ->with('message', mtrans('importcolt', 'updated_importcolt'));
    }

    /**
     * Update multiple resources
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('public_id') ?: request()->input('ids');
        $count = $this->importcoltRepo->bulk($ids, $action);

        return redirect()->to('importcolt')
            ->with('message', mtrans('importcolt', $action . '_importcolt_complete'));
    }

    /**
     * Upload and verify colt file
     */
    public function upload(ParceColtRequest $request) {
        $fileName = $request->name->getClientOriginalName();
        try {
            $coltFile = $request->name->store('colt-files');            
            $coltData = $this->coltService->parseColtFile($coltFile, true);
            foreach ($coltData as $index => $data) {
                $coltData[$index]['client'] = $this->clientRepository->getClientByDid($data['did']);
            }
        } catch(\Exception $e) {
            return response()->json([
                'name' => [$e->getMessage()]
            ], 422);
        }

        return response()->json([
            'data' => $coltData,
            'fileName' => $fileName,
            'coltFilePath' => $coltFile
        ]);
    }

    public function test() {
        $importColt = $this->importcoltRepo->getById(2);
        $job = new ParseColt(\Auth::user(), $importColt);
        dispatch($job);
        return response()->json("Hey");
    }
}
