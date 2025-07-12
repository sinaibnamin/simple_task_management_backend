<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Create roles
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $userRole = Role::firstOrCreate(['name' => 'user']);

        // Create admin user
        $admin = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345678'),
            ]
        );
        $admin->assignRole($adminRole);

        // Create Sina user manually
        $sina = User::firstOrCreate(
            ['email' => 'sina@user.com'],
            [
                'name' => 'sina ibn amin',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
            ]
        );
        $sina->assignRole($userRole);

        // Create 15 random users
        User::factory(15)->create()->each(function ($user) use ($userRole) {
            $user->assignRole($userRole);
        });
    }
}
