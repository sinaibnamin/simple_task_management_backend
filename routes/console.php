<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\SendTaskDeadlineReminders;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('tasks:send-reminders', function () {
    (new SendTaskDeadlineReminders())->handle();
})->purpose('Send email reminders for tasks due in 2 days');
