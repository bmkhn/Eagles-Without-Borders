<?php

namespace Database\Factories;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'member_id' => Member::factory(),
            'year_paid' => (int) now()->year,
            'date_paid' => now(),
        ];
    }

    /**
     * Set the payment year.
     */
    public function forYear(int $year): static
    {
        return $this->state(fn (array $attributes) => [
            'year_paid' => $year,
        ]);
    }
}
