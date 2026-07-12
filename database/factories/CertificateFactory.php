<?php

namespace Database\Factories;

use App\Models\Certificate;
use App\Models\Member;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Certificate>
 */
class CertificateFactory extends Factory
{
    protected $model = Certificate::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'name' => fake()->unique()->words(3, true) . ' Award',
            'issued_at' => now()->subMonths(rand(1, 12)),
            'file' => null,
        ];
    }

    /**
     * Set the certificate for a specific member.
     */
    public function forMember(int $memberId): static
    {
        return $this->state(fn (array $attributes) => [
            'member_id' => $memberId,
        ]);
    }

    /**
     * Give the certificate a simulated file path.
     */
    public function withFile(): static
    {
        return $this->state(fn (array $attributes) => [
            'file' => 'certificates/' . uniqid('test_') . '.webp',
        ]);
    }
}
