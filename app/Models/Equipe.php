<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipe extends Model
{
    protected $table = "T_EQUIPE";
    protected $primaryKey = "CODE_EQUIPE";
    protected $keyType = "string";
    public $incrementing = false;

    protected $guarded = [];


    public function personnel(): HasMany
    {
        return $this->hasMany(Personnel::class, 'CODE_EQUIPE', 'CODE_EQUIPE');
    }
}
