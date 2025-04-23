<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineEvent extends Model
{
    protected $table = "T_EVT_MACHINE";
    protected $primaryKey = "NO_MVT";
    protected $keyType = "string";
    public $incrementing = false;

    protected $guarded = [];

    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';

    protected $casts = [
        'DH_CREATION' => 'datetime',
        'DH_MODIF' => 'datetime',
        "DH_DEBUT" => 'datetime',
        "DATE_REF" => 'datetime',
        "DH_FIN" => 'datetime',
    ];

    // public function getDateFormat()
    // {
    //     return 'Y-m-d H:i:s.v';
    // }
}