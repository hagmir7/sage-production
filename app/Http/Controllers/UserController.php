<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request, $id){
        try {
            $validation = $request->validate([
                'name' => 'required|string|max:255',
                'full_name' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255', // Changed to nullable
                'email' => 'required|string|email|max:255|unique:users',
                'password' => 'required|string|min:8',
            ]);

            $user = User::find($id)->update([
                'name' => $validation['name'],
                'full_name' => $validation['full_name'],
                'email' => $validation['email'],
                'phone' => $validation['phone'] ?? null,
                'password' => Hash::make($validation['password']),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);
        } catch (\Exception $e) {
            // Return error as JSON response
            return response()->json([
                'message' => 'Registration failed',
                'errors' => $e->getMessage()
            ], 422);
        }
    }
}
