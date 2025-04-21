<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function update(Request $request, $id=null)
{
    try {
        // Find the user first
        if($id){
            $user = User::findOrFail($id);
        } else {
            $user = User::findOrFail(auth()->id());
            $id = auth()->id(); // Set $id to the authenticated user's ID
        }
        
        // Validate with unique email rule that ignores current user
        $validation = $request->validate([
            'name' => 'required|string|max:255',
            'full_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ]);

        // Create update data array
        $updateData = [
            'name' => $validation['name'],
            'full_name' => $validation['full_name'],
            'email' => $validation['email'],
            'phone' => $validation['phone'] ?? null,
        ];

        // Update the user
        $user->update($updateData);

        // Refresh user data from database
        $user = $user->fresh();

        return response()->json([
            'message' => 'User updated successfully',
            'token_type' => 'Bearer',
            'user' => $user
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Update failed',
            'errors' => $e->getMessage()
        ], 422);
    }
}
}
