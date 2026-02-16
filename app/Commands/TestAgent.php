<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class TestAgent extends Command
{
    protected $signature = 'agent:test {--coverage : Generate coverage report}';

    protected $description = '🧪 Test agent - Runs Pest tests with optional coverage';

    public function handle(): int
    {
        $this->banner('🧪 TEST AGENT');

        $coverage = $this->option('coverage');

        if ($coverage) {
            $this->info('Running tests with coverage...');
        } else {
            $this->info('Running tests...');
        }

        $this->newLine();

        $command = [base_path('vendor/bin/pest')];

        if ($coverage) {
            $command[] = '--coverage';
        }

        $process = new Process($command, base_path());
        $process->setTimeout(180);
        $process->run(fn ($type, $line) => $this->line($line));

        if (! $process->isSuccessful()) {
            $this->error('✗ Tests failed');

            return self::FAILURE;
        }

        $this->info('✓ All tests passed');

        if ($coverage) {
            $this->line('Coverage report generated in coverage/');
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
