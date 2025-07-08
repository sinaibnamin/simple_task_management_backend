<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        $priorities = ['low', 'normal', 'high'];
        $categories = ['work', 'personal', 'hobby'];

        return [
            'user_id' => User::inRandomOrder()->first()->id,
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->paragraph(),
            'deadline' => $this->faker->dateTimeBetween('now', '+1 month'),
            'priority' => $this->faker->randomElement($priorities),
            'category' => $this->faker->randomElement($categories),
            'is_completed' => $this->faker->boolean(30),
        ];
    }
}