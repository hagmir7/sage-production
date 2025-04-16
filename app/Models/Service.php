<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $table = "T_HORAIRE_SERVICE";
    protected $primaryKey = "CODE_SERVICE";
    protected $keyType = "string";
    public $incrementing = false;


    protected $guarded = [];


    public function personnel()
    {
        return $this->hasMany(Personnel::class, "CODE_SERVICE", "CODE_SERVICE");
    }
}
