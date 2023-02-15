<?php

namespace Delfosti\Massive\Http\Controllers;

use Error;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\MassiveUploadLogService;
use Illuminate\Support\Facades\Validator;

class MassiveUploadLogController extends Controller
{

    private $generalService;
    private $massiveUploadLogService;

    public function __construct()
    {
        $this->massiveUploadLogService = new MassiveUploadLogService();
        $this->generalService = new GeneralService();
    }

    public function show(Request $request)
    {
        try {
            $params = $request->all();
            $response = $this->massiveUploadLogService->show($params);
        } catch (Error $e) {
            $response = $e->getMessage();
        }

        // Get data
        $output = $this->generalService->processOutput($response ? 200 : 204, $response, empty($structure) ? null : $structure);

        return $output;
    }

    public function get(Request $request)
    {
        try {
            $params = $request->all();
            $response = $this->massiveUploadLogService->get($params);
        } catch (Error $e) {
            $response = $e->getMessage();
        }

        return $this->generalService->processOutput($response ? 200 : 204, $response);
    }

    public function list(Request $request)
    {
        try {
            $params = $request->all();
            $response = $this->massiveUploadLogService->list($params);
        } catch (Error $e) {
            $response = $e->getMessage();
        }

        return $this->generalService->processOutput($response ? 200 : 204, $response);
    }

    public function create(Request $request)
    {
        $body = $request->all();

        $validator = Validator::make($body, [
            'action' => 'required|min:3|max:100',
            'type' => 'required|min:3|max:10',
            'entities' => 'required',
            'upload_status' => 'required',
            'items' => 'required',
            'user_id' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return $this->generalService->processOutput(400, $validator->errors(), null, true);
        }

        // Get data
        $data = $request->all();

        $response = $this->massiveUploadLogService->create($data);

        return $this->generalService->processOutput($response ? 200 : 204, $response);
    }
}
