<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MachineGroupe extends Model
{
    protected $table = "T_MACHINE_GROUPE";
    protected $primaryKey = "CODE_GROUPE";
    protected $keyType = "string";
    public $incrementing = false;



    protected $guarded = [];


    public function machines(): HasMany
    {
        return $this->hasMany(Machine::class, "CODE_GROUPE", "CODE_GROUPE");
    }


    
}
