<?php

namespace App\Http\Controllers;

use App\Models\Machine;
use App\Models\MachineGroupe;
// use App\Models\MachineGroupe;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function index()
    {
        return Machine::orderByDesc("DH_CREATION")->get();
    }

    public function show($id)
    {
        return Machine::with(['groupe'])->find($id);
    }


    public function groupes()
    {
        return \App\Models\MachineGroupe::orderByDesc("DH_CREATION")->get();
    }

    public function show_groupe($id)
    {
        return MachineGroupe::with('machines')->find($id);
    }
}
