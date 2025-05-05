<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineEventControlController;
use App\Http\Controllers\OrderFabricationController;
use App\Http\Controllers\PersonnelController;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});



