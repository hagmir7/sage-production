<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineEventControle extends Model
{
    protected $table = "T_EVT_MACHINE_EC";
    protected $primaryKey = "id";
    protected $keyType = "string";
    public $incrementing = true;


    protected $guarded = [];


    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';


    // public function 
}
