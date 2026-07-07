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

        $members = [
            ['first_name' => 'Juan', 'middle_initial' => 'M', 'last_name' => 'Dela Cruz', 'suffix' => null],
            ['first_name' => 'Maria', 'middle_initial' => 'L', 'last_name' => 'Santos', 'suffix' => null],
            ['first_name' => 'Jose', 'middle_initial' => 'R', 'last_name' => 'Rizal', 'suffix' => 'Jr.'],
            ['first_name' => 'Ana', 'middle_initial' => null, 'last_name' => 'Gonzales', 'suffix' => null],
            ['first_name' => 'Pedro', 'middle_initial' => 'C', 'last_name' => 'Reyes', 'suffix' => null],
            ['first_name' => 'Elena', 'middle_initial' => null, 'last_name' => 'Bautista', 'suffix' => null],
            ['first_name' => 'Carlos', 'middle_initial' => 'A', 'last_name' => 'Miranda', 'suffix' => null],
            ['first_name' => 'Sofia', 'middle_initial' => 'D', 'last_name' => 'Villanueva', 'suffix' => 'III'],
            ['first_name' => 'Antonio', 'middle_initial' => null, 'last_name' => 'Garcia', 'suffix' => null],
            ['first_name' => 'Luisa', 'middle_initial' => 'F', 'last_name' => 'Fernandez', 'suffix' => null],
            ['first_name' => 'Miguel', 'middle_initial' => null, 'last_name' => 'Lopez', 'suffix' => null],
            ['first_name' => 'Carmen', 'middle_initial' => 'N', 'last_name' => 'Navarro', 'suffix' => null],
            ['first_name' => 'Ramon', 'middle_initial' => null, 'last_name' => 'Torres', 'suffix' => null],
            ['first_name' => 'Isabella', 'middle_initial' => 'R', 'last_name' => 'Rivera', 'suffix' => null],
            ['first_name' => 'Francisco', 'middle_initial' => null, 'last_name' => 'Ramos', 'suffix' => 'Sr.'],
            ['first_name' => 'Angela', 'middle_initial' => 'M', 'last_name' => 'Mendoza', 'suffix' => null],
            ['first_name' => 'Manuel', 'middle_initial' => null, 'last_name' => 'Castro', 'suffix' => null],
            ['first_name' => 'Patricia', 'middle_initial' => 'O', 'last_name' => 'Ortega', 'suffix' => null],
            ['first_name' => 'Emilio', 'middle_initial' => null, 'last_name' => 'Silva', 'suffix' => null],
            ['first_name' => 'Teresa', 'middle_initial' => 'C', 'last_name' => 'Cruz', 'suffix' => null],
        ];

        foreach ($clubs as $clubIndex => $club) {
            $createdCount = 0;

            for ($i = 0; $i < 10; $i++) {
                $memberIndex = ($clubIndex * 10 + $i) % count($members);
                $memberData = $members[$memberIndex];

                $contactNumber = '0917' . str_pad((string)($clubIndex * 10 + $i + 1), 7, '0', STR_PAD_LEFT);

                // Check if member already exists for this club with matching names
                $existing = Member::where('club_id', $club->id)
                    ->where(function ($q) use ($memberData, $contactNumber) {
                        $q->where('first_name', $memberData['first_name'])
                          ->where('last_name', $memberData['last_name'])
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
                    'first_name' => $memberData['first_name'],
                    'middle_initial' => $memberData['middle_initial'],
                    'last_name' => $memberData['last_name'],
                    'suffix' => $memberData['suffix'],
                    'status' => $i % 5 === 0 ? 'inactive' : 'active',
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
