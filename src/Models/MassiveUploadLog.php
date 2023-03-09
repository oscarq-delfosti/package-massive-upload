<?php

namespace Delfosti\Massive\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MassiveUploadLog extends Model
{
    use HasFactory;

    protected $table = "massive_upload_log";

    protected $fillable = [
        'id',
        'action',
        'entities',
        'upload_status',
        'items',
        'user_id'
    ];


    /**
     * Get the user's full name.
     *
     * @return string
     */
    public function parseEntities()
    {
        return json_decode($this->entities);
    }
}
