<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Region;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Super Admin (no region/club assignment)
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->syncRoles(['super-admin']);

        // 2. Create National Admin (no region/club assignment)
        $nationalAdmin = User::firstOrCreate(
            ['email' => 'nationaladmin@example.com'],
            [
                'name' => 'National Admin',
                'password' => Hash::make('password'),
            ]
        );
        $nationalAdmin->syncRoles(['national-admin']);

        // 3. Create the region
        $region = Region::firstOrCreate([
            'name' => 'Palawan Region 8',
        ]);

        // 4. Create Regional Admin for this region
        $regionalAdmin = User::firstOrCreate(
            ['email' => 'regionaladmin@example.com'],
            [
                'name' => 'Regional Admin - Palawan',
                'password' => Hash::make('password'),
                'region_id' => $region->id,
            ]
        );

        if (!$regionalAdmin->hasRole('regional-admin')) {
            $regionalAdmin->syncRoles(['regional-admin']);
        }

        // 5. Clubs and their respective Club Admins
        $clubs = [
            [
                'name' => 'Roxas Pangolin Eagles Club',
                'cp_name' => 'Club Admin - Roxas Pangolin EC',
                'cp_email' => 'ca.roxas@example.com',
            ],
            [
                'name' => 'Roxas Pangolin Lady Eagles Club',
                'cp_name' => 'Club Admin - Roxas Pangolin LEC',
                'cp_email' => 'ca.roxaslady@example.com',
            ],
            [
                'name' => 'Puerto de Paragua Eagles Club',
                'cp_name' => 'Club Admin - Puerto de Paragua EC',
                'cp_email' => 'ca.puerto@example.com',
            ],
            [
                'name' => 'Puerto de Paragua Lady Eagles Club',
                'cp_name' => 'Club Admin - Puerto de Paragua LEC',
                'cp_email' => 'ca.puertolady@example.com',
            ],
            [
                'name' => 'The Eagles of Australia Eagles Club',
                'cp_name' => 'Club Admin - The Eagles of Australia EC',
                'cp_email' => 'ca.australia@example.com',
            ],
        ];

        foreach ($clubs as $data) {
            $club = Club::firstOrCreate(
                ['name' => $data['name']],
                ['region_id' => $region->id]
            );

            $cp = User::updateOrCreate(
                ['email' => $data['cp_email']],
                [
                    'name' => $data['cp_name'],
                    'password' => Hash::make('password'),
                    'club_id' => $club->id,
                ]
            );

            if (!$cp->hasRole('club-admin')) {
                $cp->syncRoles(['club-admin']);
            }
        }

        $this->command->info('Admin accounts seeded: super-admin, national-admin, regional-admin, and 5 club-admins.');
    }
}
