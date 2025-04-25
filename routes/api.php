<?php

use App\Http\Controllers\AtelierController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\MachineEventControlController;
use App\Http\Controllers\OrderFabricationController;
use App\Http\Controllers\OutillageController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\PersonnelController;
use App\Http\Controllers\ProductionController;
use App\Http\Controllers\ReglageController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Models\RetoucheController;
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

Route::post('/user/update/{id}', [UserController::class, 'update']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);

    // Roles
    Route::get('/roles', [RoleController::class, 'index']);
    Route::post('/roles', [RoleController::class, 'store']);
    Route::get('/roles/permissions/{roleName}', [RoleController::class, 'permissions']);

    // Permissions
    Route::get('/permissions', [PermissionController::class, 'index']);
    Route::post('/permissions', [PermissionController::class, 'store']);

    // Assign roles/permissions to users
    Route::post('/users/{user}/roles', [UserPermissionController::class, 'assignRoles']);
    Route::post('/role/{roleName}/permissions', [UserPermissionController::class, 'assignPermissions']);

    Route::get('/user/{id}/permissions', [UserPermissionController::class, 'getUserRolesAndPermissions']);
    Route::get('/user/permissions', [UserPermissionController::class, 'getAuthUserRolesAndPermissions']);
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
    Route::get('fin_machines/{code}', 'fin_machines');
});

Route::controller(ProductionController::class)->prefix('production')->group(function () {
    Route::post('debut', 'debut');
    Route::post('fin', 'fin');
    Route::post('change', 'change');
});

Route::controller(RetoucheController::class)->prefix('retouche')->group(function () {
    Route::post('debut', 'debut');
    Route::post('fin', 'fin');
});


Route::controller(OutillageController::class)->prefix('outillage')->group(function () {
    Route::post('debut', 'debut');
    Route::post('fin', 'fin');

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
    Route::get('/filter', 'filter');
    Route::get('/{id}', 'show');
    Route::get('/nomenclature/{id}', 'nomenclature');
});


Route::controller(PersonnelController::class)->prefix('personnel')->group(function () {
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
