<?php

namespace Delfosti\Massive\Traits;

use Carbon\Carbon;

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

    public function validationErrorResponse($errors)
    {
        $structure = [];
        $structure["message"] = "The given data was invalid.";
        $structure["errors"] = $errors;

        return response()->json($structure, 422);
    }
}
