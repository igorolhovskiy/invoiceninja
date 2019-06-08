<?php

namespace Modules\Telcopackages\Http\ApiControllers;

use App\Http\Controllers\BaseAPIController;
use Modules\Telcopackages\Repositories\TelcopackagesRepository;
use Modules\Telcopackages\Http\Requests\TelcopackagesRequest;
use Modules\Telcopackages\Http\Requests\CreateTelcopackagesRequest;
use Modules\Telcopackages\Http\Requests\UpdateTelcopackagesRequest;

class TelcopackagesApiController extends BaseAPIController
{
    protected $TelcopackagesRepo;
    protected $entityType = 'telcopackages';

    public function __construct(TelcopackagesRepository $telcopackagesRepo)
    {
        parent::__construct();

        $this->telcopackagesRepo = $telcopackagesRepo;
    }

    /**
     * @SWG\Get(
     *   path="/telcopackages",
     *   summary="List telcopackages",
     *   operationId="listTelcopackagess",
     *   tags={"telcopackages"},
     *   @SWG\Response(
     *     response=200,
     *     description="A list of telcopackages",
     *      @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Telcopackages"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function index()
    {
        $data = $this->telcopackagesRepo->all();

        return $this->listResponse($data);
    }

    /**
     * @SWG\Get(
     *   path="/telcopackages/{telcopackages_id}",
     *   summary="Individual Telcopackages",
     *   operationId="getTelcopackages",
     *   tags={"telcopackages"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcopackages_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A single telcopackages",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcopackages"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function show(TelcopackagesRequest $request)
    {
        return $this->itemResponse($request->entity());
    }




    /**
     * @SWG\Post(
     *   path="/telcopackages",
     *   summary="Create a telcopackages",
     *   operationId="createTelcopackages",
     *   tags={"telcopackages"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="telcopackages",
     *     @SWG\Schema(ref="#/definitions/Telcopackages")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="New telcopackages",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcopackages"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function store(CreateTelcopackagesRequest $request)
    {
        $telcopackages = $this->telcopackagesRepo->save($request->input());

        return $this->itemResponse($telcopackages);
    }

    /**
     * @SWG\Put(
     *   path="/telcopackages/{telcopackages_id}",
     *   summary="Update a telcopackages",
     *   operationId="updateTelcopackages",
     *   tags={"telcopackages"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcopackages_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="telcopackages",
     *     @SWG\Schema(ref="#/definitions/Telcopackages")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Updated telcopackages",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcopackages"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function update(UpdateTelcopackagesRequest $request, $publicId)
    {
        if ($request->action) {
            return $this->handleAction($request);
        }

        $telcopackages = $this->telcopackagesRepo->save($request->input(), $request->entity());

        return $this->itemResponse($telcopackages);
    }


    /**
     * @SWG\Delete(
     *   path="/telcopackages/{telcopackages_id}",
     *   summary="Delete a telcopackages",
     *   operationId="deleteTelcopackages",
     *   tags={"telcopackages"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcopackages_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Deleted telcopackages",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcopackages"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function destroy(UpdateTelcopackagesRequest $request)
    {
        $telcopackages = $request->entity();

        $this->telcopackagesRepo->delete($telcopackages);

        return $this->itemResponse($telcopackages);
    }

}
