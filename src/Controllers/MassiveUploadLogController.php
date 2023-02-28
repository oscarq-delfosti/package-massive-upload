<?php

namespace Delfosti\Massive\Controllers;

use Illuminate\Http\Request;
use Delfosti\Massive\Traits\HasResponse;
use App\Http\Controllers\Controller;

use Error;

// Services
use Delfosti\Massive\Services\GeneralService;
use Delfosti\Massive\Services\MassiveUploadLogService;

class MassiveUploadLogController extends Controller
{
    use HasResponse;

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

            return $this->successResponse($response);

        } catch (Error $ex) {

            return $this->exceptionResponse(
                $ex->getMessage(),
                $ex->getLine(),
                $ex->getCode()
            );

        }
    }

    public function get(Request $request)
    {
        try {
            $params = $request->all();
            $response = $this->massiveUploadLogService->get($params);

            return $this->successResponse($response);
        } catch (Error $ex) {

            return $this->exceptionResponse(
                $ex->getMessage(),
                $ex->getLine(),
                $ex->getCode()
            );
        }
    }

    public function list(Request $request)
    {
        try {
            $params = $request->all();
            $response = $this->massiveUploadLogService->list($params);

            return $this->successResponse($response);
        } catch (Error $ex) {

            return $this->exceptionResponse(
                $ex->getMessage(),
                $ex->getLine(),
                $ex->getCode()
            );

        }
    }
}
