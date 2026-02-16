<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class QualityGateAgent extends Command
{
    protected $signature = 'agent:quality-gate {--fix : Auto-fix linting issues}';

    protected $description = '🚦 Quality gate - Runs all quality checks';

    public function handle(): int
    {
        $this->banner('🚦 QUALITY GATE');

        $fix = $this->option('fix');

        $results = [];
        $startTime = microtime(true);

        // 1. Linting
        $this->runTask('🧹 Linting', function () use ($fix, &$results) {
            $command = $fix
                ? [base_path('vendor/bin/pint')]
                : [base_path('vendor/bin/pint'), '--test'];

            $process = new Process($command, base_path());
            $process->setTimeout(120);
            $process->run();

            $results['lint'] = $process->isSuccessful();

            return $results['lint'];
        });

        // 2. Static Analysis
        $this->runTask('🔍 Static Analysis', function (&$results) {
            $process = new Process([
                base_path('vendor/bin/phpstan'),
                'analyze',
                '--memory-limit=512M',
                '--level=5',
            ], base_path());
            $process->setTimeout(180);
            $process->run();

            $results['analyze'] = $process->isSuccessful();

            return $results['analyze'];
        }, $results);

        // 3. Tests
        $this->runTask('🧪 Tests', function (&$results) {
            $process = new Process([base_path('vendor/bin/pest')], base_path());
            $process->setTimeout(180);
            $process->run();

            $results['test'] = $process->isSuccessful();

            return $results['test'];
        }, $results);

        $endTime = microtime(true);
        $duration = round($endTime - $startTime, 2);

        // Summary
        $this->newLine();
        $this->line('  ═══════════════════════════════════════════════════════════');

        $passed = array_filter($results, fn ($r) => $r);
        $failed = array_filter($results, fn ($r) => ! $r);

        if (empty($failed)) {
            $this->info("  ✅ QUALITY GATE PASSED ({$duration}s)");
            $this->newLine();

            return self::SUCCESS;
        }

        $this->error("  ❌ QUALITY GATE FAILED ({$duration}s)");
        $this->newLine();

        $this->line('  Failed checks:');
        foreach ($failed as $check => $_) {
            $this->line("    • {$check}");
        }

        $this->newLine();

        return self::FAILURE;
    }

    private function runTask(string $title, callable $task, array &$results = []): void
    {
        $this->info("  {$title}...");

        $result = $task($results);

        if ($result) {
            $this->line('    ✓');
        } else {
            $this->line('    ✗');
        }
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
