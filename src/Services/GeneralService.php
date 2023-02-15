<?php

namespace Delfosti\Massive\Services;

use DateTime;

class GeneralService
{

    public function capitalizeEntity(string $entity)
    {

        $arrEntity = explode('_', $entity);
        $entity = "";

        foreach ($arrEntity as $item) {
            $entity .= ucwords($item);
        }

        return $entity;
    }

    public function processResponse($data)
    {
        $response = [];

        if (count($data['confirmed']) == 0) {
            $response["message"] = "No item has been confirmed";
            $response["status"] = false;
            $response["code"] = 400;
        } else if (count($data['confirmed']) > 0 && count($data['failed']) > 0) {
            $response["message"] = "Some items have not been confirmed";
        } else if (count($data['confirmed']) > 0 && count($data['failed']) == 0) {
            $response["message"] = "All items confirmed";
        }

        $response['totalConfirmed'] = count($data['confirmed']);
        $response['totalFailed'] = count($data['failed']);

        $response['items'] = $data;

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

    public function removeDiferentKeys($model, &$item)
    {

        foreach ($item as $key => $i) {
            if (array_search($key, $model) === false) {
                unset($item[$key]);
            }
        }

    }

    public function sortAssociativeArray(&$array, $sortBy)
    {

        foreach ($array as $key => $row) {
            $aux[$key] = $row[$sortBy];
        }

        array_multisort($aux, SORT_ASC, $array);
    }

    public function filterDate(&$query, $args)
    {
        $date_field = $args['date_field'] ?? 'created_at';
        $date = $args['date'] ?? null;
        $date_end = $args['date_end'] ?? null;

        if ($date && $date_end === null) {
            $date_time = strtotime($date);
            $date = new DateTime(date('Y-m-d\T', $date_time) . '00:00:00');
            $date_end = new DateTime(date('Y-m-d\T', $date_time) . '23:59:59');
            $query->whereBetween($date_field, [$date, $date_end]);
        } elseif ($date === null && $date_end) {
            $date_end = !empty($date_end) ? new DateTime($date_end) : null;
            $query->where($date_field, '<=', $date_end);
        } elseif ($date && $date_end) {
            $date = !empty($date) ? new DateTime($date) : null;
            $date_end = !empty($date_end) ? new DateTime($date_end . '+1 day') : null;
            $query->whereBetween($date_field, [$date, $date_end]);
        }
    }

    public function processOutput($code, $response = null, $structure = null, $errors = null)
    {
        $output = [
            'code' => $code,
            'status' => (! $errors && $response) ? 'success' : 'fail',
            'data' => $response,
        ];

        // Structure
        if ($structure) {
            if (is_bool($structure)) {
                unset($output['data']);
                $output['structure'] = $response;
            } else {
                $output['structure'] = $structure;
            }
        }

        // Errors
        if ($errors) {
            unset($output['data']);
            $output['errors'] = $response;
        }

        return response()->json($output, $code);
    }
}
