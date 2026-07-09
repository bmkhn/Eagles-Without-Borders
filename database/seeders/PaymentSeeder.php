<?php

namespace Database\Seeders;

use App\Models\Member;
use App\Models\Payment;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Seed payment records for testing the auto-status system.
     *
     * - 80% of members get a payment for the current year (→ active)
     * - 20% get no current year payment (→ inactive)
     * - ~60% also get a previous year payment for history
     * - National President always gets a current year payment
     * - Uses firstOrCreate so it's safe to re-run
     */
    public function run(): void
    {
        $currentYear = (int) now()->year;
        $previousYear = $currentYear - 1;

        $members = Member::with('club')->get();

        if ($members->isEmpty()) {
            $this->command->warn('No members found. Skipping Payment seeder.');
            return;
        }

        $created = 0;
        $total = $members->count();

        foreach ($members as $index => $member) {
            $isNationalPresident = is_null($member->club_id);

            // National President always gets current year payment
            // Other members: 80% get current year payment (indices not divisible by 5)
            $getsCurrentYearPayment = $isNationalPresident || ($index % 5 !== 0);

            if ($getsCurrentYearPayment) {
                $payment = Payment::firstOrCreate(
                    ['member_id' => $member->id, 'year_paid' => $currentYear],
                    ['date_paid' => now()->subDays(rand(1, 180))]
                );
                if ($payment->wasRecentlyCreated) {
                    $created++;
                }
            }

            // ~60% get previous year payment too (even those inactive this year)
            if ($index % 5 < 3) {
                $payment = Payment::firstOrCreate(
                    ['member_id' => $member->id, 'year_paid' => $previousYear],
                    ['date_paid' => now()->subYear()->subDays(rand(1, 180))]
                );
                if ($payment->wasRecentlyCreated) {
                    $created++;
                }
            }

            // Sync status based on payments
            $member->updateStatusFromPayments();
        }

        $activeCount = Member::where('status', 'active')->count();
        $inactiveCount = $total - $activeCount;

        $this->command->info("Created {$created} new payment records for {$total} members.");
        $this->command->info("Result: {$activeCount} active, {$inactiveCount} inactive (auto-managed by payments).");
    }
}
