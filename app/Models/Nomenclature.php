<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Nomenclature extends Model
{
    protected $table = "T_ORDREFAB_NOMENC";
    protected $primaryKey = "id";
    protected $keyType = 'string';
    public $incrementing = false;

    public function of()
    {
        return $this->belongsTo(OrderFabrication::class);
    }
}
