<?php

namespace Delfosti\Massive\Services;

use DateTime;

class GeneralService
{
    public function sortAssociativeArray(&$array, $sortBy)
    {

        foreach ($array as $key => $row) {
            $aux[$key] = $row[$sortBy];
        }

        array_multisort($aux, SORT_ASC, $array);
    }

    public function removeDiferentKeys($model, &$item)
    {

        foreach ($item as $key => $i) {
            if (array_search($key, $model) === false) {
                unset($item[$key]);
            }
        }

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
}
