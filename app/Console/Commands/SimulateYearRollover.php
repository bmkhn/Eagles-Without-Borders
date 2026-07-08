<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class SimulateYearRollover extends Command
{
    protected $signature = 'members:simulate-rollover
                            {--year= : The new year to simulate (defaults to next year)}';
    protected $description = 'Simulate a new year rollover for testing — inactivates members who haven\'t paid for a given year';

    public function handle(): int
    {
        $year = (int) ($this->option('year') ?? (now()->year + 1));
        $currentYear = now()->year;

        $this->info("============================================");
        $this->info("  YEAR ROLLOVER SIMULATION");
        $this->info("============================================");
        $this->line("");
        $this->line("  Simulating rollover to year: <fg=yellow>{$year}</>");
        $this->line("  Current actual year:         {$currentYear}");
        $this->line("");

        // Find active members who haven't paid for the simulated year
        $membersToInactivate = Member::query()
            ->where('status', 'active')
            ->whereDoesntHave('payments', function ($q) use ($year) {
                $q->where('year_paid', $year);
            })
            ->get();

        $totalActive = Member::where('status', 'active')->count();
        $totalPaid = Member::where('status', 'active')
            ->whereHas('payments', fn ($q) => $q->where('year_paid', $year))
            ->count();

        $this->line("  Active members:          <fg=cyan>{$totalActive}</>");
        $this->line("  Already paid for {$year}:   <fg=green>{$totalPaid}</>");
        $this->line("  Will be inactivated:     <fg=red>{$membersToInactivate->count()}</>");
        $this->line("");

        if ($membersToInactivate->isEmpty()) {
            $this->info("  ✅ No members to inactivate. All active members have paid for {$year}.");
            $this->line("");
            return Command::SUCCESS;
        }

        // Show preview
        $this->line("  Preview of members to be inactivated:");
        $this->line("  ───────────────────────────────────────────");
        foreach ($membersToInactivate->take(10) as $member) {
            $this->line("    • {$member->name} ({$member->club?->name})");
        }
        if ($membersToInactivate->count() > 10) {
            $this->line("    ... and " . ($membersToInactivate->count() - 10) . " more");
        }
        $this->line("");

        // Confirm
        if (!$this->confirm("  Proceed with inactivating {$membersToInactivate->count()} member(s)?", false)) {
            $this->warn("  Cancelled.");
            return Command::SUCCESS;
        }

        $this->line("");

        // Process
        $bar = $this->output->createProgressBar($membersToInactivate->count());
        $bar->start();

        foreach ($membersToInactivate as $member) {
            $member->update(['status' => 'inactive']);

            activity('payment')
                ->performedOn($member)
                ->withProperties([
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'year' => $year,
                    'action' => 'auto_inactivate_simulated',
                    'note' => 'Triggered via simulate-rollover command',
                ])
                ->log('member_inactivated_unpaid');

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info("  ✅ Done! Inactivated {$membersToInactivate->count()} member(s).");
        $this->line("");
        $this->line("  Tip: Record payments for {$year} in the member edit page,");
        $this->line("  then re-run this command to verify they stay active.");
        $this->line("");

        return Command::SUCCESS;
    }
}
