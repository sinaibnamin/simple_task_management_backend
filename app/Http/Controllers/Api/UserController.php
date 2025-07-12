<?php

namespace App\Http\Controllers\Api;

use App\Events\UserNameUpdated;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    public function index(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());
            $users = User::where('id', '!=', 1)->withCount('tasks')->with('roles')->get();
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

    public function show(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::with('roles')->findOrFail($id);
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

    public function update(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->id,
                'password' => 'nullable|min:6',
            ]);

            if (isset($validated['password']) && $validated['password'] !== '') {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $user->update($validated);

            if (isset($validated['name'])) {
                event(new UserNameUpdated($user->id, $user->name));
            }

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

    public function editProfile(Request $request)
    {
        try {
            $user = $request->user();

            $freshUser = User::findOrFail($user->id);

            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $freshUser->id,
                'password' => 'nullable|string|min:6',
            ]);

            if (isset($validated['password']) && $validated['password'] !== '') {
                $validated['password'] = bcrypt($validated['password']);
            } else {
                unset($validated['password']);
            }

            $freshUser->update($validated);

            return response()->json([
                'status' => 'success',
                'data' => $freshUser,
                'message' => 'Profile updated successfully.',
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
                'message' => 'Error updating profile',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(Request $request, $id)
    {
        try {
            $this->authorizeAdmin($request->user());
            $user = User::findOrFail($id);

            if ($user->tasks()->exists()) {
                throw new Exception("Cannot delete user because they have assigned tasks.");
            }

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
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function create(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|string|min:6|confirmed',
            ]);

            $validated['password'] = bcrypt($validated['password']);

            $user = User::create($validated);

            $user->assignRole('user');

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

    public function taskAnalytics(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());

            $users = User::withCount('tasks')
                ->orderByDesc('tasks_count')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $users,
                'message' => 'Task analytics retrieved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving task analytics',
                'data' => null,
            ], 500);
        }
    }

    public function upcomingDeadlineTasks(Request $request)
    {
        try {
            $this->authorizeAdmin($request->user());

            $tasks = Task::where('deadline', '>=', now())
                ->where('is_completed', false)
                ->orderBy('deadline', 'asc')
                ->limit(10)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $tasks,
                'message' => 'Top 10 upcoming incomplete tasks retrieved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving tasks with upcoming deadlines',
                'data' => null,
            ], 500);
        }
    }

    public function upcomingUserDeadlineTasks(Request $request)
    {
        try {
            $user = $request->user();

            $tasks = Task::where('user_id', $user->id)
                ->where('deadline', '>=', now())
                ->where('is_completed', false)
                ->orderBy('deadline', 'asc')
                ->limit(2)
                ->get();

            return response()->json([
                'status' => 'success',
                'data' => $tasks,
                'message' => 'Your upcoming tasks retrieved successfully.',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving your upcoming tasks',
                'data' => null,
            ], 500);
        }
    }


    private function authorizeAdmin($user)
    {
        if (!$user->hasRole('admin')) {
            abort(403, 'Unauthorized');
        }
    }

}
