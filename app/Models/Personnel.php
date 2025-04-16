<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Personnel extends Model
{
    protected $table = "T_PERSONNEL";
    protected $primaryKey = "CODE_PERS";
    protected $keyType = "string";
    public $incrementing = false;


    protected $guarded = [];
}
