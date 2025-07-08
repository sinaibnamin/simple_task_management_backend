<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    // Get all users
    public function index(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());
            $users = User::withCount('tasks')->get();
            return response()->json([
                'status' => 'success',
                'data' => $users,
                'message' => 'Users retrieved successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Users not found',
                'data' => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving users',
                'data' => null,
            ], 500);
        }
    }

    // Show a specific user
    public function show(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::findOrFail($id);
            return response()->json([
                'status' => 'success',
                'data' => $user->load('tasks'),
                'message' => 'User retrieved successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found',
                'data' => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving user',
                'data' => null,
            ], 500);
        }
    }

    // Update an existing user
    public function update(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::findOrFail($id); // Find user or fail with ModelNotFoundException

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'nullable|min:6',
            ]);

            if (isset($validated['password'])) {
                $validated['password'] = bcrypt($validated['password']);
            }

            $user->update($validated);

            return response()->json([
                'status' => 'success',
                'data' => $user,
                'message' => 'User updated successfully.',
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found',
                'data' => null,
            ], 404);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'data' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error updating user',
                'data' => null,
            ], 500);
        }
    }

    // Delete a user
    public function destroy(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::findOrFail($id); // Find user or fail with ModelNotFoundException
            $user->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'User deleted successfully',
                'data' => null,
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found',
                'data' => null,
            ], 404);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error deleting user',
                'data' => null,
            ], 500);
        }
    }

    // Create a new user
    public function create(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());

            // Validate user input
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $validated['password'] = bcrypt($validated['password']);
            $user = User::create($validated);

            return response()->json([
                'status' => 'success',
                'data' => $user,
                'message' => 'User created successfully.',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'data' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error creating user',
                'data' => null,
            ], 500);
        }
    }

    // Check if the user is an admin
    private function authorizeAdmin($user)
    {
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
    }
}
