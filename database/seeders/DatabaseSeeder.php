<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed networks first
        $this->call([
            NetworksSeeder::class,
        ]);

        // Create main user
        $mainUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Main User',
                'password' => Hash::make('password'),
                'created_by' => null,
                'parent_user_id' => null,
            ]
        );

        // Create sub-users
        User::firstOrCreate(
            ['email' => 'sub1@example.com'],
            [
                'name' => 'Sub User 1',
                'password' => Hash::make('password'),
                'created_by' => $mainUser->id,
                'parent_user_id' => $mainUser->id,
            ]
        );

        User::firstOrCreate(
            ['email' => 'sub2@example.com'],
            [
                'name' => 'Sub User 2',
                'password' => Hash::make('password'),
                'created_by' => $mainUser->id,
                'parent_user_id' => $mainUser->id,
            ]
        );

        $this->command->info('✅ Database seeded successfully!');
        $this->command->info('📧 Main User: admin@example.com / password');
        $this->command->info('📧 Sub User 1: sub1@example.com / password');
        $this->command->info('📧 Sub User 2: sub2@example.com / password');
    }
}
