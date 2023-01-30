<?php

namespace Delfosti\Massive\Controllers;

use App\Http\Controllers\Controller;
use Delfosti\Massive\Traits\HasResponse;

use Delfosti\Massive\Services\MassiveService;

use Delfosti\Massive\Requests\Massive\{CreateRequest, UpdateRequest, DeleteRequest};

class MassiveController extends Controller
{
    use HasResponse;
    private $massiveService;

    public function __construct()
    {
        $this->massiveService = new MassiveService();
    }

    public function create(CreateRequest $request)
    {

        $body = $request->all();

        $response = $this->massiveService->create($body);

        return $this->defaultResponse($response);

    }

    public function update(UpdateRequest $request)
    {

        $body = $request->all();

        $response = $this->massiveService->update($body);

        return $this->defaultResponse($response);
    }

    public function delete(DeleteRequest $request)
    {

        $body = $request->all();

        $response = $this->massiveService->delete($body);

        return $this->defaultResponse($response);
    }
}
