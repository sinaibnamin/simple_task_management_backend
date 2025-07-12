<!DOCTYPE html>
<html>
<head>
    <title>Task Reminder</title>
</head>
<body>
    <p>Hello {{ $task->user->name }},</p>

    <p>This is a reminder that the following task is due in 2 days:</p>

    <ul>
        <li><strong>Title:</strong> {{ $task->title }}</li>
        <li><strong>Description:</strong> {{ $task->description }}</li>
        <li><strong>Deadline:</strong> {{ $task->deadline->format('F j, Y') }}</li>
    </ul>

    <p>Please ensure you complete it before the deadline.</p>

    <p>Regards,<br>Your Task Manager</p>
</body>
</html>
