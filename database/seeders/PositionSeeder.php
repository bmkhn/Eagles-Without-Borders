<?php

namespace Database\Seeders;

use App\Models\Position;
use Illuminate\Database\Seeder;

class PositionSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            'Club President',
            'National President',
            'Secretary General',
            'Regional Assemblyman',
            'National Assemblyman',
            'Care Officer',
            'Member',
            'Public Information Officer',
            'Peace Officer',
            'Board of Directors',
            'Club Secretary',
            'Tribunal Grievance Chairman',
            'Tribunal Grievance Member',
            'Club Vice President',
            'Care Chairman',
            'Auditor',
        ];

        foreach ($positions as $name) {
            Position::firstOrCreate(['name' => $name]);
        }

        $this->command->info('Seeded ' . count($positions) . ' positions.');
    }
}
