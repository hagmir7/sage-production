<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::all());
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:roles']);
        $role = Role::create(['name' => $request->name, 'guard_name' => "web"]);
        return response()->json($role);
    }


    public function permissions($roleName)
    {
        try {

            $role = Role::findByName($roleName, 'web');
            $permissions = $role->permissions->map(function ($perm) {
                return [
                    'id' => $perm->id,
                    'name' => $perm->name
                ];
            });


            return response()->json([
                'role' => $role->name,
                'permissions' => $permissions
            ]);
        } catch (\Spatie\Permission\Exceptions\RoleDoesNotExist $e) {
            return response()->json([
                'error' => "Role '{$roleName}' not found for the 'web' guard."
            ], 404);
        }
    }
}
