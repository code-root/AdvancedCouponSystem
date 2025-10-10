<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create main user
        $mainUser = User::create([
            'name' => 'Main User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'created_by' => null,
            'parent_user_id' => null,
        ]);

        // Create sub-users
        User::create([
            'name' => 'Sub User 1',
            'email' => 'sub1@example.com',
            'password' => Hash::make('password'),
            'created_by' => $mainUser->id,
            'parent_user_id' => $mainUser->id,
        ]);

        User::create([
            'name' => 'Sub User 2',
            'email' => 'sub2@example.com',
            'password' => Hash::make('password'),
            'created_by' => $mainUser->id,
            'parent_user_id' => $mainUser->id,
        ]);
    }
}
