<?php

namespace Database\Seeders;

use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Seeder;

class MemberSeeder extends Seeder
{
    public function run(): void
    {
        $clubs = Club::all();
        $positions = Position::all();

        if ($clubs->isEmpty()) {
            $this->command->warn('No clubs found. Skipping Member seeder.');
            return;
        }

        if ($positions->isEmpty()) {
            $this->command->warn('No positions found. Skipping Member seeder.');
            return;
        }

        $memberNames = [
            'Juan Dela Cruz',
            'Maria Santos',
            'Jose Rizal',
            'Ana Gonzales',
            'Pedro Reyes',
            'Elena Bautista',
            'Carlos Miranda',
            'Sofia Villanueva',
            'Antonio Garcia',
            'Luisa Fernandez',
            'Miguel Lopez',
            'Carmen Navarro',
            'Ramon Torres',
            'Isabella Rivera',
            'Francisco Ramos',
            'Angela Mendoza',
            'Manuel Castro',
            'Patricia Ortega',
            'Emilio Silva',
            'Teresa Cruz',
        ];

        $baseContact = '09170000000';

        foreach ($clubs as $clubIndex => $club) {
            $createdCount = 0;

            for ($i = 0; $i < 10; $i++) {
                $nameIndex = ($clubIndex * 10 + $i) % count($memberNames);
                $name = $memberNames[$nameIndex];

                $contactNumber = '0917' . str_pad((string)($clubIndex * 10 + $i + 1), 7, '0', STR_PAD_LEFT);

                // Check if member already exists for this club with a matching name or contact
                $existing = Member::where('club_id', $club->id)
                    ->where(function ($q) use ($name, $contactNumber) {
                        $q->where('name', $name)
                          ->orWhere('contact_number', $contactNumber);
                    })
                    ->first();

                if ($existing) {
                    continue;
                }

                $position = $positions->get($i % $positions->count());

                $member = new Member([
                    'club_id' => $club->id,
                    'position_id' => $position->id,
                    'name' => $name,
                    'contact_number' => $contactNumber,
                ]);

                $member->applySlugFromName();
                $member->save();
                $createdCount++;
            }

            $this->command->info("Created {$createdCount} members for club: {$club->name}");
        }
    }
}
