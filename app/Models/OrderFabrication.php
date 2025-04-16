<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderFabrication extends Model
{
    protected $table = "T_ORDREFAB";
    protected $primaryKey  = "CODE_OF";
    public $incrementing = false;
    protected $keyType = 'string';


    public function nomenclatures()
    {
        return $this->hasMany(Nomenclature::class, 'CODE_OF', 'CODE_OF');
    }


}
