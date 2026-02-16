<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class AnalyzeAgent extends Command
{
    protected $signature = 'agent:analyze {--level=5 : PHPStan level}';

    protected $description = '🔍 Static analysis agent - Runs PHPStan';

    public function handle(): int
    {
        $this->banner('🔍 ANALYZE AGENT');

        $level = $this->option('level');

        $this->info("Running PHPStan (level {$level})...");
        $this->newLine();

        $process = new Process([
            base_path('vendor/bin/phpstan'),
            'analyze',
            '--memory-limit=512M',
            "--level={$level}",
        ], base_path());

        $process->setTimeout(180);
        $process->run(fn ($type, $line) => $this->line($line));

        if (! $process->isSuccessful()) {
            $this->error('✗ Static analysis failed');

            return self::FAILURE;
        }

        $this->info('✓ Static analysis passed');

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
