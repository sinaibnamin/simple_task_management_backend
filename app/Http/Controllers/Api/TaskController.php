<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TaskService;
use App\Models\Task;
use Illuminate\Validation\ValidationException;
use Exception;

class TaskController extends Controller
{
    protected $service;

    public function __construct(TaskService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        try {
            $tasks = $this->service->getUserTasks($request->user(), $request->all());
            return response()->json([
                'status' => 'success',
                'data' => $tasks,
                'message' => 'Tasks retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving tasks',
                'data' => null,
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:255',
                'deadline' => 'required|date',
                'priority' => 'required|in:low,normal,high',
                'category' => 'required|string|max:100',
            ]);

            $task = $this->service->createTask($request->user(), $validated);

            return response()->json([
                'status' => 'success',
                'data' => $task,
                'message' => 'Task created successfully',
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
                'message' => 'Error creating task',
                'data' => null,
            ], 500);
        }
    }

    public function show(Task $task)
    {
        try {
            $this->authorizeTask($task);
            return response()->json([
                'status' => 'success',
                'data' => $task,
                'message' => 'Task retrieved successfully',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error retrieving task',
                'data' => null,
            ], 500);
        }
    }

    public function update(Request $request, Task $task)
    {
        try {
            $this->authorizeTask($task);

            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string|max:255',
                'deadline' => 'sometimes|date',
                'priority' => 'sometimes|in:low,normal,high',
                'category' => 'sometimes|string|max:100',
                'is_completed' => 'nullable|boolean',
            ]);

            $task = $this->service->updateTask($task, $validated);

            return response()->json([
                'status' => 'success',
                'data' => $task,
                'message' => 'Task updated successfully',
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Validation error',
                'data' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Error updating task',
                'data' => null,
            ], 500);
        }
    }

    public function destroy(Task $task)
    {
        try {
            $this->authorizeTask($task);
            $this->service->deleteTask($task);

            return response()->json([
                'status' => 'success',
                'message' => 'Task deleted successfully',
                'data' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    public function markCompleted(Task $task)
    {
        try {
            $this->authorizeTask($task);
            $task = $this->service->markCompleted($task);

            return response()->json([
                'status' => 'success',
                'data' => $task,
                'message' => 'Task marked as completed',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'fail',
                'message' => $e->getMessage(),
                'data' => null,
            ], 500);
        }
    }

    private function authorizeTask(Task $task)
    {
        if (auth()->user()->hasRole('admin')) {
            return;
        }
        if ($task->user_id !== auth()->id()) {
            abort(403, 'Unauthorized');
        }
    }
}
