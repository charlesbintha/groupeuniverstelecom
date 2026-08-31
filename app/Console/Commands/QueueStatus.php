<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class QueueStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'queue:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Display queue status (pending, failed jobs)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Queue Status Report');
        $this->newLine();

        // Pending jobs
        $pendingJobs = DB::table('jobs')->count();
        $this->line("📋 Pending Jobs: <fg=yellow>{$pendingJobs}</>");

        if ($pendingJobs > 0) {
            $this->newLine();
            $this->line('Last 5 pending jobs:');
            $jobs = DB::table('jobs')
                ->orderBy('id', 'desc')
                ->limit(5)
                ->get(['id', 'queue', 'payload', 'created_at']);

            foreach ($jobs as $job) {
                $payload = json_decode($job->payload, true);
                $jobClass = $payload['displayName'] ?? 'Unknown';
                $this->line("  - Job #{$job->id}: {$jobClass} (Queue: {$job->queue})");
            }
        }

        $this->newLine();

        // Failed jobs
        $failedJobs = DB::table('failed_jobs')->count();

        if ($failedJobs > 0) {
            $this->line("❌ Failed Jobs: <fg=red>{$failedJobs}</>");
            $this->newLine();

            $this->line('Last 5 failed jobs:');
            $failed = DB::table('failed_jobs')
                ->orderBy('failed_at', 'desc')
                ->limit(5)
                ->get(['id', 'connection', 'queue', 'exception', 'failed_at']);

            foreach ($failed as $fail) {
                // Extract first line of exception
                $exceptionLines = explode("\n", $fail->exception);
                $firstLine = $exceptionLines[0] ?? 'Unknown error';

                $this->line("  - Failed Job #{$fail->id} at {$fail->failed_at}");
                $this->line("    Error: " . substr($firstLine, 0, 100));
            }

            $this->newLine();
            $this->warn('💡 Tip: Run `php artisan queue:retry all` to retry failed jobs');
        } else {
            $this->line("✅ Failed Jobs: <fg=green>0</>");
        }

        $this->newLine();

        // Queue connection
        $queueConnection = config('queue.default');
        $this->line("🔗 Queue Connection: <fg=cyan>{$queueConnection}</>");

        // Database check
        if ($queueConnection === 'database') {
            $tablesExist = $this->checkDatabaseTables();
            if ($tablesExist) {
                $this->line("✅ Database tables: <fg=green>OK</>");
            } else {
                $this->error('❌ Database tables missing! Run: php artisan queue:table && php artisan migrate');
            }
        }

        $this->newLine();

        return Command::SUCCESS;
    }

    /**
     * Check if queue database tables exist.
     */
    protected function checkDatabaseTables(): bool
    {
        try {
            return DB::table('jobs')->count() >= 0 && DB::table('failed_jobs')->count() >= 0;
        } catch (\Exception $e) {
            return false;
        }
    }
}
