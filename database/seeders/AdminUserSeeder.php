<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed only the top-level admin accounts:
     * one super-admin and one national-admin.
     */
    public function run(): void
    {
        // Super Admin (no region/club assignment)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        // National Admin (no region/club assignment)
        $nationalAdmin = User::firstOrCreate(
            ['email' => 'nationaladmin@example.com'],
            [
                'name' => 'National Admin',
                'password' => Hash::make('password'),
            ]
        );
        $nationalAdmin->syncRoles(['national-admin']);

        $this->command->info('Admin accounts seeded: super-admin and national-admin.');
    }
}
