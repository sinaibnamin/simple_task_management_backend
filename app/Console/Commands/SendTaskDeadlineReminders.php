<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Task;
use Illuminate\Support\Facades\Mail;
use App\Mail\TaskReminderMail;
use Carbon\Carbon;

class SendTaskDeadlineReminders extends Command
{
    protected $signature = 'tasks:send-reminders';
    protected $description = 'Send email reminders for tasks due in 2 days';

    public function handle()
    {
        $targetDate = Carbon::now()->addDays(2)->startOfDay();
        
        $tasks = Task::with('user')
            ->whereDate('deadline', $targetDate)
            ->where('is_completed', false)
            ->get();

        foreach ($tasks as $task) {
            if ($task->user && $task->user->email) {
                Mail::to($task->user->email)->send(new TaskReminderMail($task));
                $this->info("Email sent to: {$task->user->email}");
            }
        }

        return Command::SUCCESS;
    }
}
