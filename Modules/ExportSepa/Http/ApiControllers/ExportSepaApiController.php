<?php

namespace Modules\ExportSepa\Http\ApiControllers;

use App\Http\Controllers\BaseAPIController;
use Modules\Exportsepa\Repositories\ExportsepaRepository;
use Modules\Exportsepa\Http\Requests\ExportsepaRequest;
use Modules\Exportsepa\Http\Requests\CreateExportsepaRequest;
use Modules\Exportsepa\Http\Requests\UpdateExportsepaRequest;

class ExportsepaApiController extends BaseAPIController
{
    protected $ExportsepaRepo;
    protected $entityType = 'exportsepa';

    public function __construct(ExportsepaRepository $exportsepaRepo)
    {
        parent::__construct();

        $this->exportsepaRepo = $exportsepaRepo;
    }

    /**
     * @SWG\Get(
     *   path="/exportsepa",
     *   summary="List exportsepa",
     *   operationId="listExportsepas",
     *   tags={"exportsepa"},
     *   @SWG\Response(
     *     response=200,
     *     description="A list of exportsepa",
     *      @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Exportsepa"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function index()
    {
        $data = $this->exportsepaRepo->all();

        return $this->listResponse($data);
    }

    /**
     * @SWG\Get(
     *   path="/exportsepa/{exportsepa_id}",
     *   summary="Individual Exportsepa",
     *   operationId="getExportsepa",
     *   tags={"exportsepa"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="exportsepa_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A single exportsepa",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Exportsepa"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function show(ExportsepaRequest $request)
    {
        return $this->itemResponse($request->entity());
    }




    /**
     * @SWG\Post(
     *   path="/exportsepa",
     *   summary="Create a exportsepa",
     *   operationId="createExportsepa",
     *   tags={"exportsepa"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="exportsepa",
     *     @SWG\Schema(ref="#/definitions/Exportsepa")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="New exportsepa",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Exportsepa"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function store(CreateExportsepaRequest $request)
    {
        $exportsepa = $this->exportsepaRepo->save($request->input());

        return $this->itemResponse($exportsepa);
    }

    /**
     * @SWG\Put(
     *   path="/exportsepa/{exportsepa_id}",
     *   summary="Update a exportsepa",
     *   operationId="updateExportsepa",
     *   tags={"exportsepa"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="exportsepa_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="exportsepa",
     *     @SWG\Schema(ref="#/definitions/Exportsepa")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Updated exportsepa",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Exportsepa"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function update(UpdateExportsepaRequest $request, $publicId)
    {
        if ($request->action) {
            return $this->handleAction($request);
        }

        $exportsepa = $this->exportsepaRepo->save($request->input(), $request->entity());

        return $this->itemResponse($exportsepa);
    }


    /**
     * @SWG\Delete(
     *   path="/exportsepa/{exportsepa_id}",
     *   summary="Delete a exportsepa",
     *   operationId="deleteExportsepa",
     *   tags={"exportsepa"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="exportsepa_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Deleted exportsepa",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Exportsepa"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function destroy(UpdateExportsepaRequest $request)
    {
        $exportsepa = $request->entity();

        $this->exportsepaRepo->delete($exportsepa);

        return $this->itemResponse($exportsepa);
    }

}
