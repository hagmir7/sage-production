<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFabrication extends Model
{
    protected $table = "T_ORDREFAB";
    protected $primaryKey  = "CODE_OF";
    public $incrementing = false;
    protected $keyType = 'string';

    protected $guarded = [];


    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';


    public function nomenclatures()
    {
        return $this->hasMany(Nomenclature::class, 'CODE_OF', 'CODE_OF');
    }

    public function operations()
    {
        return $this->hasMany(Operation::class, "CODE_OF", "CODE_OF");
    }
}
