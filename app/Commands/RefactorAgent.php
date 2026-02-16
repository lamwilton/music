<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class RefactorAgent extends Command
{
    protected $signature = 'agent:refactor {--dry-run : Show changes without applying them}';

    protected $description = '⚡ Refactoring agent - Runs Rector to automatically improve code';

    public function handle(): int
    {
        $this->banner('⚡ REFACTOR AGENT');

        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->info('Running Rector in dry-run mode...');
        } else {
            $this->info('Running Rector to refactor code...');
        }

        $this->newLine();

        $command = [base_path('vendor/bin/rector')];

        if ($dryRun) {
            $command[] = '--dry-run';
        }

        $command[] = '--memory-limit=512M';

        $process = new Process($command, base_path());
        $process->setTimeout(300);
        $process->run(fn ($type, $line) => $this->line($line));

        if (! $process->isSuccessful()) {
            $this->error('✗ Refactoring failed');

            return self::FAILURE;
        }

        if ($dryRun) {
            $this->info('✓ No issues found (or run without --dry-run to apply fixes)');
        } else {
            $this->info('✓ Refactoring complete');
        }

        return self::SUCCESS;
    }

    private function banner(string $title): void
    {
        $this->newLine();
        $this->line('  ╔═══════════════════════════════════════════════════════════╗');
        $this->line("  ║  {$title}");
        $this->line('  ╚═══════════════════════════════════════════════════════════╝');
        $this->newLine();
    }
}
