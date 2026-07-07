<?php

namespace Database\Seeders;

use App\Models\Certificate;
use App\Models\Member;
use Illuminate\Database\Seeder;

class CertificateSeeder extends Seeder
{
    public function run(): void
    {
        $members = Member::all();

        if ($members->isEmpty()) {
            $this->command->warn('No members found. Skipping Certificate seeder.');
            return;
        }

        $certificateTemplates = [
            [
                'name' => 'Leadership Excellence Award',
                'generateFile' => true,
            ],
            [
                'name' => 'Community Service Recognition',
                'generateFile' => false,
            ],
            [
                'name' => 'Fraternal Service Award',
                'generateFile' => true,
            ],
        ];

        $created = 0;

        foreach ($members as $member) {
            $count = rand(1, 3);

            for ($i = 0; $i < $count && $i < count($certificateTemplates); $i++) {
                $template = $certificateTemplates[$i];

                $existing = Certificate::where('member_id', $member->id)
                    ->where('name', $template['name'])
                    ->exists();

                if ($existing) {
                    continue;
                }

                $data = [
                    'member_id' => $member->id,
                    'name' => $template['name'],
                    'issued_at' => now()->subMonths(rand(1, 12))->subDays(rand(0, 28)),
                ];

                // Some certificates get a simulated file path
                if ($template['generateFile']) {
                    $data['file'] = 'certificates/' . uniqid('seed_') . '.webp';
                }

                Certificate::create($data);

                $created++;
            }
        }

        $this->command->info("Created {$created} certificates across {$members->count()} members.");
    }
}
