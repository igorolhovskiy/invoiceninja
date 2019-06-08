<?php

namespace Modules\Telcorates\Http\ApiControllers;

use App\Http\Controllers\BaseAPIController;
use Modules\Telcorates\Repositories\TelcoratesRepository;
use Modules\Telcorates\Http\Requests\TelcoratesRequest;
use Modules\Telcorates\Http\Requests\CreateTelcoratesRequest;
use Modules\Telcorates\Http\Requests\UpdateTelcoratesRequest;

class TelcoratesApiController extends BaseAPIController
{
    protected $TelcoratesRepo;
    protected $entityType = 'telcorates';

    public function __construct(TelcoratesRepository $telcoratesRepo)
    {
        parent::__construct();

        $this->telcoratesRepo = $telcoratesRepo;
    }

    /**
     * @SWG\Get(
     *   path="/telcorates",
     *   summary="List telcorates",
     *   operationId="listTelcoratess",
     *   tags={"telcorates"},
     *   @SWG\Response(
     *     response=200,
     *     description="A list of telcorates",
     *      @SWG\Schema(type="array", @SWG\Items(ref="#/definitions/Telcorates"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function index()
    {
        $data = $this->telcoratesRepo->all();

        return $this->listResponse($data);
    }

    /**
     * @SWG\Get(
     *   path="/telcorates/{telcorates_id}",
     *   summary="Individual Telcorates",
     *   operationId="getTelcorates",
     *   tags={"telcorates"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcorates_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="A single telcorates",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcorates"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function show(TelcoratesRequest $request)
    {
        return $this->itemResponse($request->entity());
    }




    /**
     * @SWG\Post(
     *   path="/telcorates",
     *   summary="Create a telcorates",
     *   operationId="createTelcorates",
     *   tags={"telcorates"},
     *   @SWG\Parameter(
     *     in="body",
     *     name="telcorates",
     *     @SWG\Schema(ref="#/definitions/Telcorates")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="New telcorates",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcorates"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function store(CreateTelcoratesRequest $request)
    {
        $telcorates = $this->telcoratesRepo->save($request->input());

        return $this->itemResponse($telcorates);
    }

    /**
     * @SWG\Put(
     *   path="/telcorates/{telcorates_id}",
     *   summary="Update a telcorates",
     *   operationId="updateTelcorates",
     *   tags={"telcorates"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcorates_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Parameter(
     *     in="body",
     *     name="telcorates",
     *     @SWG\Schema(ref="#/definitions/Telcorates")
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Updated telcorates",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcorates"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function update(UpdateTelcoratesRequest $request, $publicId)
    {
        if ($request->action) {
            return $this->handleAction($request);
        }

        $telcorates = $this->telcoratesRepo->save($request->input(), $request->entity());

        return $this->itemResponse($telcorates);
    }


    /**
     * @SWG\Delete(
     *   path="/telcorates/{telcorates_id}",
     *   summary="Delete a telcorates",
     *   operationId="deleteTelcorates",
     *   tags={"telcorates"},
     *   @SWG\Parameter(
     *     in="path",
     *     name="telcorates_id",
     *     type="integer",
     *     required=true
     *   ),
     *   @SWG\Response(
     *     response=200,
     *     description="Deleted telcorates",
     *      @SWG\Schema(type="object", @SWG\Items(ref="#/definitions/Telcorates"))
     *   ),
     *   @SWG\Response(
     *     response="default",
     *     description="an ""unexpected"" error"
     *   )
     * )
     */
    public function destroy(UpdateTelcoratesRequest $request)
    {
        $telcorates = $request->entity();

        $this->telcoratesRepo->delete($telcorates);

        return $this->itemResponse($telcorates);
    }

}
