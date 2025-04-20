<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineEventControlController;
use App\Http\Controllers\OrderFabricationController;
use App\Http\Controllers\PersonnelController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('login-error', [AuthController::class, 'loginError'])->name('login');



Route::controller(MachineController::class)
    ->prefix('machines')
    ->group(function () {
        Route::get('', 'index');
        Route::get('/groupes', 'groupes');
        Route::get('/groupe/{id}', 'show_groupe');
        Route::get('/{id}', 'show'); // <<< Put this last
    });



Route::controller(OrderFabricationController::class)->prefix('orders-fabrication')->group(function () {
    Route::get('', 'index');
    Route::get('/{id}', 'show');
    Route::get('/nomenclature/{id}', 'nomenclature');
});


Route::controller(PersonnelController::class)->prefix('personnel')->group(function(){
    Route::get('/', 'index');
    Route::get('/category', 'category');
    Route::get('/category/{id}', 'personnel_category');
    Route::get('/equipe', 'equipe');
    Route::get('/equipe/{id}', 'personnel_equipe');
    Route::get('/service', 'service');
    Route::get('/service/{id}', 'personnel_service');
});


Route::controller(MachineEventControlController::class)->prefix('machine-event-control')->group(function () {
    Route::get('', 'index');
    Route::get('/{id}', 'show');
    // Route::get('/nomenclature/{id}', 'nomenclature');
});
