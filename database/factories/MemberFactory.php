<?php

namespace Database\Factories;

use App\Models\Club;
use App\Models\Member;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Member>
 */
class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();

        return [
            'club_id' => Club::factory(),
            'position_id' => Position::factory(),
            'first_name' => $firstName,
            'middle_initial' => fake()->optional(0.4)->randomLetter(),
            'last_name' => $lastName,
            'suffix' => fake()->optional(0.1)->randomElement(['Jr.', 'Sr.', 'III']),
            'status' => 'inactive',
            'slug' => Member::generateUniqueSlug($firstName, $lastName),
            'contact_number' => '0917' . fake()->numerify('#######'),
        ];
    }

    /**
     * Assign the member to a specific club.
     */
    public function forClub(int $clubId): static
    {
        return $this->state(fn (array $attributes) => [
            'club_id' => $clubId,
        ]);
    }
}
