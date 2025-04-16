<?php

namespace App\Http\Controllers;

use App\Models\MachineEventControle;
use Illuminate\Http\Request;

class MachineEventControlController extends Controller
{
    public function index()
    {
        return MachineEventControle::orderByDesc("DH_CREATION")->get();
    }


    public function show($id)
    {
        return MachineEventControle::find($id);
    }
}
