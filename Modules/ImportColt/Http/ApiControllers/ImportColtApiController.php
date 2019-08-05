<?php

namespace Modules\ImportColt\Http\ApiControllers;

use App\Http\Controllers\BaseAPIController;
use Modules\Importcolt\Repositories\ImportcoltRepository;
use Modules\Importcolt\Http\Requests\ImportcoltRequest;
use Modules\Importcolt\Http\Requests\CreateImportcoltRequest;
use Modules\Importcolt\Http\Requests\UpdateImportcoltRequest;

class ImportcoltApiController extends BaseAPIController
{
    protected $ImportcoltRepo;
    protected $entityType = 'importcolt';

    public function __construct(ImportcoltRepository $importcoltRepo)
    {
        parent::__construct();

        $this->importcoltRepo = $importcoltRepo;
    }

    /**
     * @SWG\Get(
     *   path="/importcolt",
     *   summary="List importcolt",
     *   operationId="listImportcolts",
     *   tags={"importcolt"},
     *   @SWG\Response(
     *     response=200,
     *     description="A list of importcolt",
     *      @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Importcolt"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function index()
    {
        $data = $this->importcoltRepo->all();

        return $this->listResponse($data);
    }

    /**
     * @SWG\Get(
     *   path="/importcolt/{importcolt_id}",
     *   summary="Individual Importcolt",
     *   operationId="getImportcolt",
     *   tags={"importcolt"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="importcolt_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A single importcolt",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Importcolt"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function show(ImportcoltRequest $request)
    {
        return $this->itemResponse($request->entity());
    }




    /**
     * @SWG\Post(
     *   path="/importcolt",
     *   summary="Create a importcolt",
     *   operationId="createImportcolt",
     *   tags={"importcolt"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="importcolt",
     *     @SWG\Schema(ref="#/definitions/Importcolt")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="New importcolt",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Importcolt"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function store(CreateImportcoltRequest $request)
    {
        $importcolt = $this->importcoltRepo->save($request->input());

        return $this->itemResponse($importcolt);
    }

    /**
     * @SWG\Put(
     *   path="/importcolt/{importcolt_id}",
     *   summary="Update a importcolt",
     *   operationId="updateImportcolt",
     *   tags={"importcolt"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="importcolt_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="importcolt",
     *     @SWG\Schema(ref="#/definitions/Importcolt")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Updated importcolt",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Importcolt"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function update(UpdateImportcoltRequest $request, $publicId)
    {
        if ($request->action) {
            return $this->handleAction($request);
        }

        $importcolt = $this->importcoltRepo->save($request->input(), $request->entity());

        return $this->itemResponse($importcolt);
    }


    /**
     * @SWG\Delete(
     *   path="/importcolt/{importcolt_id}",
     *   summary="Delete a importcolt",
     *   operationId="deleteImportcolt",
     *   tags={"importcolt"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="importcolt_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Deleted importcolt",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Importcolt"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function destroy(UpdateImportcoltRequest $request)
    {
        $importcolt = $request->entity();

        $this->importcoltRepo->delete($importcolt);

        return $this->itemResponse($importcolt);
    }

}
