<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class LintAgent extends Command
{
    protected $signature = 'agent:lint {--fix : Automatically fix linting issues}';

    protected $description = '🧹 Linting agent - Runs Pint to check code style';

    public function handle(): int
    {
        $this->banner('🧹 LINT AGENT');

        $fix = $this->option('fix');

        $this->info('Running Pint...');
        $this->newLine();

        $command = $fix
            ? [base_path('vendor/bin/pint')]
            : [base_path('vendor/bin/pint'), '--test'];

        $process = new Process($command, base_path());
        $process->setTimeout(120);
        $process->run(fn ($type, $line) => $this->line($line));

        if (! $process->isSuccessful()) {
            $this->error('✗ Linting failed');

            if (! $fix) {
                $this->line('Run: spotify agent:lint --fix to auto-fix');
            }

            return self::FAILURE;
        }

        $this->info('✓ Linting passed');

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
