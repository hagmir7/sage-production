<?php

use App\Http\Controllers\AtelierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReglageController;
use App\Http\Controllers\UserController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\ValidationException;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::get('/user', [AuthController::class, 'user']);
Route::get('/user/{id}', [AuthController::class, 'show']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/user/update', [UserController::class, 'update']);
    Route::post('/user/update/{id}', [UserController::class, 'update']);
});



Route::get('/users', function (Request $request) {
    return User::all();
});


Route::post('/sanctum/token', function (Request $request) {
    $request->validate([
        'email' => 'required|email',
        'password' => 'required',
        'device_name' => 'required',
    ]);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
        throw ValidationException::withMessages([
            'email' => ['The provided credentials are incorrect.'],
        ]);
    }

    return $user->createToken($request->device_name)->plainTextToken;
});



Route::controller(AtelierController::class)->prefix('atelier')->group(function () {
    Route::get('', 'index');
    Route::get('/{id}', 'show');
    Route::put('/update/{id}', 'update');
    Route::post('/store', 'store');
});


Route::controller(ReglageController::class)->prefix('reglage')->group(function () {
    Route::post('debut', 'debut');
    Route::post('fin', 'fin');
});

