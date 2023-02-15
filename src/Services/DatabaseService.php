<?php

namespace Delfosti\Massive\Services;

use Illuminate\Support\Facades\DB;

class DatabaseService
{

    /**
     * Summary of findByField
     * @param string $table
     * @param string $field
     * @param string $value
     * @return \Illuminate\Database\Concerns\BuildsQueries|\Illuminate\Database\Eloquent\Model|null|object
     */
    public function findByField($table, $field, $value)
    {
        $item = DB::table($table)->where($field, $value)->first();

        if(!$item){
            return null;
        }

        return $item;
    }

}
