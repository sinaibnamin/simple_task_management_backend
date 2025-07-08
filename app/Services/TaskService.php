<?php

namespace App\Services;

use App\Models\Task;

class TaskService
{
    public function getUserTasks($user, $filters = [])
    {
        $query = Task::where('user_id', $user->id);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (isset($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }
        if (isset($filters['status'])) {
            if ($filters['status'] === 'completed') {
                $query->where('is_completed', true);
            } elseif ($filters['status'] === 'pending') {
                $query->where('is_completed', false);
            }
        }
        return $query->latest()->get();
    }

    public function createTask($user, $data)
    {
        $data['user_id'] = $user->id;
        return Task::create($data);
    }

    public function updateTask(Task $task, $data)
    {
        $task->update($data);
        return $task;
    }

    public function deleteTask(Task $task)
    {
        return $task->delete();
    }

    public function markCompleted(Task $task)
    {
        $task->is_completed = true;
        $task->save();
        return $task;
    }
}
