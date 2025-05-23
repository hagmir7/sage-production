<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Machine extends Model
{
    protected $table = 'T_MACHINE';
    protected $primaryKey = 'CODE_MACHINE';
    protected $keyType = 'string';
    public $incrementing = false;


    const CREATED_AT = 'DH_CREATION';
    const UPDATED_AT = 'DH_MODIF';


    protected $guarded = [];


    public function groupe(): BelongsTo
    {
        return $this->belongsTo(MachineGroupe::class, "CODE_GROUPE");
    }


    public function current_events(){
        return $this->hasMany(MachineEventControle::class, 'CODE_MACHINE', 'CODE_MACHINE');
    }
}
