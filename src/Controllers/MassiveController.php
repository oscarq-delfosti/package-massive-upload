<?php

namespace Delfosti\Massive\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Delfosti\Massive\Traits\HasResponse;
use Illuminate\Support\Facades\Validator;

use Delfosti\Massive\Services\MassiveService;
use Delfosti\Massive\Services\ModelService;

class MassiveController extends Controller
{
    use HasResponse;
    private $massiveService;
    private $modelService;

    public function __construct()
    {
        $this->massiveService = new MassiveService();
        $this->modelService = new ModelService();
    }

    public function getFunctionanilities(Request $request)
    {
        $params = $request->all();
        $response = $this->massiveService->getFunctionalities($params);

        return response()->json($response, $response['code']);
    }

    public function getModels(Request $request)
    {
        $params = $request->all();
        $response = $this->massiveService->getModels($params);

        return response()->json($response);
    }

    public function uploader(Request $request)
    {

        $body = $request->all();

        $validate = Validator::make($body, [
            'action' => 'required',
            'items' => 'required|array'
        ]);

        if ($validate->fails()) {
            return $this->validationErrorResponse($validate->errors());
        }

        $body['domain'] = $request->getSchemeAndHttpHost();

        $response = $this->massiveService->uploader($body);

        return response()->json($response);

    }

}
