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

        // Only 1 National President member across all clubs
        $nationalPresidentPosition = $positions->firstWhere('name', 'National President');
        $otherPositions = $positions->filter(fn($p) => $p->name !== 'National President')->values();

        // 50 unique name combinations — no duplicates across the entire database
        $members = [
            ['first_name' => 'Juan',       'middle_initial' => 'M', 'last_name' => 'Dela Cruz',    'suffix' => null],
            ['first_name' => 'Maria',      'middle_initial' => 'L', 'last_name' => 'Santos',       'suffix' => null],
            ['first_name' => 'Jose',       'middle_initial' => 'R', 'last_name' => 'Rizal',        'suffix' => 'Jr.'],
            ['first_name' => 'Ana',        'middle_initial' => null,'last_name' => 'Gonzales',     'suffix' => null],
            ['first_name' => 'Pedro',      'middle_initial' => 'C', 'last_name' => 'Reyes',        'suffix' => null],
            ['first_name' => 'Elena',      'middle_initial' => null,'last_name' => 'Bautista',     'suffix' => null],
            ['first_name' => 'Carlos',     'middle_initial' => 'A', 'last_name' => 'Miranda',      'suffix' => null],
            ['first_name' => 'Sofia',      'middle_initial' => 'D', 'last_name' => 'Villanueva',   'suffix' => 'III'],
            ['first_name' => 'Antonio',    'middle_initial' => null,'last_name' => 'Garcia',       'suffix' => null],
            ['first_name' => 'Luisa',      'middle_initial' => 'F', 'last_name' => 'Fernandez',    'suffix' => null],
            ['first_name' => 'Miguel',     'middle_initial' => null,'last_name' => 'Lopez',        'suffix' => null],
            ['first_name' => 'Carmen',     'middle_initial' => 'N', 'last_name' => 'Navarro',      'suffix' => null],
            ['first_name' => 'Ramon',      'middle_initial' => null,'last_name' => 'Torres',       'suffix' => null],
            ['first_name' => 'Isabella',   'middle_initial' => 'R', 'last_name' => 'Rivera',       'suffix' => null],
            ['first_name' => 'Francisco',  'middle_initial' => null,'last_name' => 'Ramos',        'suffix' => 'Sr.'],
            ['first_name' => 'Angela',     'middle_initial' => 'M', 'last_name' => 'Mendoza',      'suffix' => null],
            ['first_name' => 'Manuel',     'middle_initial' => null,'last_name' => 'Castro',       'suffix' => null],
            ['first_name' => 'Patricia',   'middle_initial' => 'O', 'last_name' => 'Ortega',       'suffix' => null],
            ['first_name' => 'Emilio',     'middle_initial' => null,'last_name' => 'Silva',        'suffix' => null],
            ['first_name' => 'Teresa',     'middle_initial' => 'C', 'last_name' => 'Cruz',         'suffix' => null],
            ['first_name' => 'Andres',     'middle_initial' => 'B', 'last_name' => 'Bonifacio',    'suffix' => null],
            ['first_name' => 'Gabriela',   'middle_initial' => null,'last_name' => 'Silang',       'suffix' => null],
            ['first_name' => 'Lapu',       'middle_initial' => null,'last_name' => 'Lapu',         'suffix' => null],
            ['first_name' => 'Melchora',   'middle_initial' => 'A', 'last_name' => 'Aquino',       'suffix' => null],
            ['first_name' => 'Apolinario', 'middle_initial' => null,'last_name' => 'Mabini',       'suffix' => null],
            ['first_name' => 'Graciano',   'middle_initial' => null,'last_name' => 'Lopez-Jaena',  'suffix' => null],
            ['first_name' => 'Marcelo',    'middle_initial' => 'H', 'last_name' => 'Del Pilar',    'suffix' => null],
            ['first_name' => 'Antonio',    'middle_initial' => null,'last_name' => 'Luna',         'suffix' => null],
            ['first_name' => 'Emilio',     'middle_initial' => 'F', 'last_name' => 'Aguinaldo',    'suffix' => null],
            ['first_name' => 'Josefa',     'middle_initial' => null,'last_name' => 'Llanes-Escoda','suffix' => null],
            ['first_name' => 'Fernando',   'middle_initial' => null,'last_name' => 'Amorsolo',     'suffix' => null],
            ['first_name' => 'Ruth',       'middle_initial' => 'A', 'last_name' => 'De Leon',      'suffix' => null],
            ['first_name' => 'Ricardo',    'middle_initial' => null,'last_name' => 'Mercado',      'suffix' => null],
            ['first_name' => 'Leticia',    'middle_initial' => 'B', 'last_name' => 'Dimagiba',     'suffix' => null],
            ['first_name' => 'Dante',      'middle_initial' => null,'last_name' => 'Macapagal',    'suffix' => null],
            ['first_name' => 'Nena',       'middle_initial' => 'S', 'last_name' => 'Rosales',      'suffix' => null],
            ['first_name' => 'Rogelio',    'middle_initial' => null,'last_name' => 'Sarmiento',    'suffix' => null],
            ['first_name' => 'Cecilia',    'middle_initial' => 'T', 'last_name' => 'Muñoz',        'suffix' => null],
            ['first_name' => 'Reynaldo',   'middle_initial' => null,'last_name' => 'Villamor',     'suffix' => null],
            ['first_name' => 'Lourdes',    'middle_initial' => 'P', 'last_name' => 'Quisumbing',   'suffix' => null],
            ['first_name' => 'Benjamin',   'middle_initial' => null,'last_name' => 'Santillan',    'suffix' => null],
            ['first_name' => 'Rosario',    'middle_initial' => 'V', 'last_name' => 'Cortez',       'suffix' => null],
            ['first_name' => 'Eduardo',    'middle_initial' => null,'last_name' => 'Panganiban',   'suffix' => null],
            ['first_name' => 'Guadalupe',  'middle_initial' => null,'last_name' => 'Valencia',     'suffix' => null],
            ['first_name' => 'Felipe',     'middle_initial' => 'N', 'last_name' => 'Agbayani',     'suffix' => null],
            ['first_name' => 'Perlita',    'middle_initial' => null,'last_name' => 'Marquez',      'suffix' => null],
            ['first_name' => 'Leandro',    'middle_initial' => 'G', 'last_name' => 'Fernandez',    'suffix' => null],
            ['first_name' => 'Aurora',     'middle_initial' => null,'last_name' => 'Matias',       'suffix' => null],
            ['first_name' => 'Gerardo',    'middle_initial' => null,'last_name' => 'Martinez',     'suffix' => null],
            ['first_name' => 'Victoria',   'middle_initial' => 'E', 'last_name' => 'Samson',       'suffix' => null],
        ];

        // Create the single National President member (first club)
        if ($nationalPresidentPosition && $clubs->isNotEmpty()) {
            $npData = array_shift($members); // Take Juan Dela Cruz out of the pool
            $np = new Member([
                'club_id' => null, // National President has no club
                'position_id' => $nationalPresidentPosition->id,
                'first_name' => $npData['first_name'],
                'middle_initial' => $npData['middle_initial'],
                'last_name' => $npData['last_name'],
                'suffix' => $npData['suffix'],
                'status' => 'inactive', // auto-managed by payments
                'contact_number' => '09170001001',
            ]);
            $np->applySlugFromName();
            $np->save();
            $this->command->info('Created 1 National President member.');
        }

        $contactBase = 1002;

        foreach ($clubs as $clubIndex => $club) {
            $createdCount = 0;

            for ($i = 0; $i < 10; $i++) {
                $memberIndex = $clubIndex * 10 + $i;
                $memberData = $members[$memberIndex % count($members)];

                $contactNumber = '0917' . str_pad((string)($contactBase + $memberIndex), 7, '0', STR_PAD_LEFT);

                $position = $otherPositions->isNotEmpty()
                    ? $otherPositions->get($i % $otherPositions->count())
                    : $positions->get($i % $positions->count());

                $member = new Member([
                    'club_id' => $club->id,
                    'position_id' => $position->id,
                    'first_name' => $memberData['first_name'],
                    'middle_initial' => $memberData['middle_initial'],
                    'last_name' => $memberData['last_name'],
                    'suffix' => $memberData['suffix'],
                    'status' => 'inactive', // auto-managed by payments
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
