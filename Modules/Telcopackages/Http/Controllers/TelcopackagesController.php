<?php

namespace Modules\Telcopackages\Http\Controllers;

use Auth;
use App\Http\Controllers\BaseController;
use App\Services\DatatableService;
use Modules\Telcopackages\Datatables\TelcopackagesDatatable;
use Modules\Telcopackages\Repositories\TelcopackagesRepository;
use Modules\Telcopackages\Http\Requests\TelcopackagesRequest;
use Modules\Telcopackages\Http\Requests\CreateTelcopackagesRequest;
use Modules\Telcopackages\Http\Requests\UpdateTelcopackagesRequest;

class TelcopackagesController extends BaseController
{
    protected $TelcopackagesRepo;
    //protected $entityType = 'telcopackages';

    public function __construct(TelcopackagesRepository $telcopackagesRepo)
    {
        //parent::__construct();

        $this->telcopackagesRepo = $telcopackagesRepo;
    }

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        return view('list_wrapper', [
            'entityType' => 'telcopackages',
            'datatable' => new TelcopackagesDatatable(),
            'title' => mtrans('telcopackages', 'telcopackages_list'),
        ]);
    }

    public function datatable(DatatableService $datatableService)
    {
        $search = request()->input('sSearch');
        $userId = Auth::user()->filterId();

        $datatable = new TelcopackagesDatatable();
        $query = $this->telcopackagesRepo->find($search, $userId);

        return $datatableService->createDatatable($datatable, $query);
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create(TelcopackagesRequest $request)
    {
        $data = [
            'telcopackages' => null,
            'method' => 'POST',
            'url' => 'telcopackages',
            'title' => mtrans('telcopackages', 'new_telcopackages'),
        ];

        return view('telcopackages::edit', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function store(CreateTelcopackagesRequest $request)
    {
        $telcopackages = $this->telcopackagesRepo->save($request->input());

        return redirect()->to($telcopackages->present()->editUrl)
            ->with('message', mtrans('telcopackages', 'created_telcopackages'));
    }

    /**
     * Show the form for editing the specified resource.
     * @return Response
     */
    public function edit(TelcopackagesRequest $request)
    {
        $telcopackages = $request->entity();
        $data = [
            'telcopackages' => $telcopackages,
            'method' => 'PUT',
            'url' => 'telcopackages/' . $telcopackages->public_id,
            'title' => mtrans('telcopackages', 'edit_telcopackages'),
        ];

        return view('telcopackages::edit', $data);
    }

    /**
     * Show the form for editing a resource.
     * @return Response
     */
    public function show(TelcopackagesRequest $request)
    {
        return redirect()->to("telcopackages/{$request->telcopackages}/edit");
    }

    /**
     * Update the specified resource in storage.
     * @param  Request $request
     * @return Response
     */
    public function update(UpdateTelcopackagesRequest $request)
    {
        $telcopackages = $this->telcopackagesRepo->save($request->input(), $request->entity());

        return redirect()->to($telcopackages->present()->editUrl)
            ->with('message', mtrans('telcopackages', 'updated_telcopackages'));
    }

    /**
     * Update multiple resources
     */
    public function bulk()
    {
        $action = request()->input('action');
        $ids = request()->input('public_id') ?: request()->input('ids');
        $count = $this->telcopackagesRepo->bulk($ids, $action);

        return redirect()->to('telcopackages')
            ->with('message', mtrans('telcopackages', $action . '_telcopackages_complete'));
    }
}
