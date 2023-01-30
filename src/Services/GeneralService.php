<?php

namespace Delfosti\Massive\Services;

use Illuminate\Support\Facades\Log;
use Throwable;

class GeneralService
{

    function logDebug(Throwable $exception, array $extra = null)
    {
        try {
            Log::debug(
                $exception->getMessage(),
                [
                    'Trace' => "
                    'File' => {$exception->getTrace()[0]['file']},
                    'Line' => {$exception->getTrace()[0]['line']},
                    'Function' => {$exception->getTrace()[0]['function']},
                    'Class' => {$exception->getTrace()[0]['class']},
                    'Type' => {$exception->getTrace()[0]['type']}
                    ",
                    'extra' => $extra ?? 'El mejor CMS',
                ]
            );
        } catch (Throwable $exception) {
            Log::emergency('Hay problemas con los logs: ', [$exception->getMessage()]);
        }
    }

    public function capitalizeEntity(string $entity)
    {

        $arrEntity = explode('_', $entity);
        $entity = "";

        foreach ($arrEntity as $item) {
            $entity .= ucwords($item);
        }

        return $entity;
    }

    public function processResponse($data, $message = "", $status = true, $code = 200)
    {

        $response['message'] = $message;
        $response['status'] = $status;
        $response['code'] = $code;

        if ($data) {

            if (count($data['confirmed']) == 0) {
                $message = "No item has been confirmed";
                $status = false;
                $code = 400;
            } else if (count($data['confirmed']) > 0 && (count($data['invalid']) > 0 || count($data['failed']) > 0)) {
                $message = "Some items have not been confirmed";
            } else if (count($data['confirmed']) > 0 && (count($data['invalid']) == 0 || count($data['failed']) == 0)) {
                $message = "All items confirmed";
            }

            $data['totalConfirmed'] = count($data['confirmed']);
            $data['totalInvalid'] = count($data['invalid']);
            $data['totalFailed'] = count($data['failed']);

            $response['items'] = $data;
        }

        return $response;

    }

    public function removeItemsFromArray(&$array, $items)
    {

        foreach ($items as $item) {
            if (($key = array_search($item, $array)) !== false) {
                unset($array[$key]);
            }
        }

    }

}
