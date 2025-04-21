<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Atelier extends Model
{
    protected $table = "T_ATELIER";
    protected $primaryKey = "CODE_ATELIER";
    protected $keyType = "string";
    public $incrementing = false;
    
    protected  $guarded = [];

    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';

    public function machines(){
        return $this->hasMany(Machine::class, "CODE_ATELIER", 'CODE_ATELIER');
    }

}
