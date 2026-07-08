<?php

namespace App\Console\Commands;

use App\Models\Member;
use Illuminate\Console\Command;

class ProcessYearRollover extends Command
{
    protected $signature = 'members:process-year-rollover
                            {--year= : The year to process (defaults to current year)}
                            {--force : Process even if not January 1}';
    protected $description = 'Inactivate members who have not paid for the specified year';

    public function handle(): int
    {
        $year = (int) ($this->option('year') ?? now()->year);

        // Safety guard: only run automatically on Jan 1 unless forced
        if (!$this->option('force') && !(now()->month === 1 && now()->day === 1)) {
            $this->warn('Year rollover only runs automatically on January 1.');
            $this->line('Use --force to run manually, or --year to specify a different year.');
            return Command::SUCCESS;
        }
        $this->info("Processing year rollover for {$year}...");

        // Find all active members who have NOT paid for this year
        $membersToInactivate = Member::query()
            ->where('status', 'active')
            ->whereDoesntHave('payments', function ($q) use ($year) {
                $q->where('year_paid', $year);
            })
            ->get();

        $count = $membersToInactivate->count();

        if ($count === 0) {
            $this->info("All active members have paid for {$year}. No changes needed.");
            return Command::SUCCESS;
        }

        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($membersToInactivate as $member) {
            $member->update(['status' => 'inactive']);

            activity('payment')
                ->performedOn($member)
                ->withProperties([
                    'member_id' => $member->id,
                    'member_name' => $member->name,
                    'year' => $year,
                    'action' => 'auto_inactivate',
                ])
                ->log('member_inactivated_unpaid');

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Inactivated {$count} member(s) who haven't paid for {$year}.");

        return Command::SUCCESS;
    }
}
