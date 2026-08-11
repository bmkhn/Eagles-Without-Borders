<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Shared-hosting cron entry point
|--------------------------------------------------------------------------
|
| This file is the single trigger for the site's scheduled jobs. The
| Hostinger cron job runs this file ONCE PER DAY — no per-minute scheduler
| invocation is required.
|
| What it runs (each time the cron fires):
|
|   - members:process-year-rollover  — the January 1 membership rollover.
|       The command has a built-in safety guard: on any day other than
|       January 1 it does nothing, so running it daily is harmless.
|       On Jan 1 it inactivates members who have not paid for the new year.
|   - activitylog:prune              — deletes activity logs older than
|       one year (housekeeping; a little work each day).
|
| To add more jobs later, add the Artisan command name to the $commands
| array below.
|
| Hostinger hPanel setup:
|   1. hPanel -> Advanced -> Cron Jobs -> Add Cron Job
|   2. Schedule: once per day (e.g. 00:30)
|   3. Command:
|        php /home/u247912231/domains/navajowhite-spoonbill-670073.hostingersite.com/public_html/cron.php
|      ...or via the "PHP script" picker, point it at: public_html/cron.php
|
| This file lives OUTSIDE the public document root (public_html/public), so
| it cannot be triggered over the web. As an extra safety net, it also
| refuses to run unless invoked from the command line.
|
*/

use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\ConsoleOutput;

// Safety: only allow CLI execution. Web requests can never run the jobs.
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Access denied. This script may only be run from the command line.');
}

define('LARAVEL_START', microtime(true));

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

// The Artisan commands to run on every cron invocation.
$commands = [
    'members:process-year-rollover',
    'activitylog:prune',
];

$exitCode = 0;

foreach ($commands as $command) {
    $status = $kernel->handle(new StringInput($command), new ConsoleOutput());

    if ($status !== 0) {
        $exitCode = $status;
    }
}

exit($exitCode);
