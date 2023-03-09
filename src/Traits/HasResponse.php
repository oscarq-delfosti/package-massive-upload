<?php

namespace Delfosti\Massive\Traits;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

trait HasResponse
{

    /**
     * @param string $message
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    public function defaultResponse($data)
    {
        $data = [
            'status' => $data['status'] ?? true,
            'code' => $data['code'] ?? 200,
            'message' => $data['message'] ?? 'OK',
            'timestamp' => Carbon::now()->toDateTimeString(),
            'items' => $data['items'] ?? []
        ];

        return response()->json($data, $data['code']);

    }

    public function defaultStructure($code)
    {
        return [
            'code' => $code,
            'status' => ($code == 200) ? 'success' : 'fail',
            'message' => helper_get_status($code),
        ];

    }

    public function successResponse($data, $message = null, $code = 200)
    {

        $response["message"] = $message ?? "OK";
        $response["data"] = collect($data)->toArray();

        return response()->json($response, $code);
    }

    public function exceptionResponse($message, $line, $code)
    {
        $data["message"] = $message ?? "OK";
        $data["line"] = $line;

        return response()->json($data, $code);
    }

    public function validationErrorResponse($errors)
    {
        $structure = [];
        $structure["message"] = "The given data was invalid.";
        $structure["errors"] = $errors;

        return response()->json($structure, 422);
    }

    public function resourceResponse(JsonResource $resource, $code)
    {
        return $resource->additional($this->defaultStructure($code));
    }
}
