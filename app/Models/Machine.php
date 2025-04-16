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


    public function groupe(): BelongsTo
    {
        return $this->belongsTo(MachineGroupe::class, "CODE_GROUPE");
    }
}
