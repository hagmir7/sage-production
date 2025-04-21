<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Operation extends Model
{
    protected $table = "T_OPERATION";
    protected $primaryKey = "CODE_OP";
    protected $keyType = "string";
    public $incrementing = false;

    protected $guarded = [];


    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';
}
