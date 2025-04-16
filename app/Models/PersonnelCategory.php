<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonnelCategory extends Model
{
    protected $table = "T_PERSONNEL_CATEGORIE";
    protected $primaryKey = "CODE_CATEGORIE";
    protected $keyType = "string";
    public $incrementing = false;


    protected $guarded = [];


    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class, 'CODE_CATEGORIE', 'CODE_CATEGORIE');
    }
}
