<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;
use Symfony\Component\Process\Process;

class DevAgent extends Command
{
    protected $signature = 'dev {task? : What to do (fix, review, test, all)} {--fix : Auto-fix issues}';

    protected $description = '🧙‍♂️ Dev Agent - Smart assistant that runs and orchestrates all quality tools';

    private array $results = [];

    public function handle(): int
    {
        $task = $this->argument('task') ?? 'all';
        $fix = $this->option('fix');

        $this->banner('🧙‍♂️ DEV AGENT');

        return match ($task) {
            'fix' => $this->runFixes(),
            'review' => $this->runReview(),
            'test' => $this->runTestsOnly(),
            'all' => $this->runAll($fix),
            default => $this->invalidTask($task),
        };
    }

    private function runAll(bool $fix): int
    {
        $startTime = microtime(true);

        $this->runLint($fix);
        $this->runAnalyze();
        $this->runTestsInternal();

        if ($fix) {
            $this->analyzeAndFix();
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->displaySummary($duration);

        $failed = array_filter($this->results, fn ($r) => ! $r['success']);

        return empty($failed) ? self::SUCCESS : self::FAILURE;
    }

    private function runLint(bool $fix): void
    {
        $this->info('🧹 Running lint...');

        $cmd = $fix
            ? [base_path('vendor/bin/pint')]
            : [base_path('vendor/bin/pint'), '--test'];

        $process = new Process($cmd, base_path());
        $process->setTimeout(120);
        $process->run();

        $this->results['lint'] = [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
        ];

        $this->line($this->results['lint']['success'] ? '  ✓ Lint passed' : '  ✗ Lint failed');
    }

    private function runAnalyze(): void
    {
        $this->info('🔍 Running static analysis...');

        $process = new Process([
            base_path('vendor/bin/phpstan'),
            'analyze',
            '--memory-limit=512M',
            '--level=5',
        ], base_path());

        $process->setTimeout(180);
        $process->run();

        $this->results['analyze'] = [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
        ];

        $this->line($this->results['analyze']['success'] ? '  ✓ Analysis passed' : '  ✗ Analysis found issues');
    }

    private function runTestsInternal(): void
    {
        $this->info('🧪 Running tests...');

        $process = new Process([base_path('vendor/bin/pest')], base_path());
        $process->setTimeout(180);
        $process->run();

        $this->results['test'] = [
            'success' => $process->isSuccessful(),
            'output' => $process->getOutput(),
        ];

        $this->line($this->results['test']['success'] ? '  ✓ Tests passed' : '  ✗ Tests failed');
    }

    private function analyzeAndFix(): void
    {
        $issues = [];

        foreach ($this->results as $name => $result) {
            if (! $result['success'] && isset($result['output'])) {
                $issues[$name] = $result['output'];
            }
        }

        if (empty($issues)) {
            $this->info('✅ All issues already fixed!');

            return;
        }

        if (! $this->results['analyze']['success']) {
            $this->info('⚡ Running Rector to auto-fix issues...');

            $process = new Process([
                base_path('vendor/bin/rector'),
                '--memory-limit=512M',
            ], base_path());
            $process->setTimeout(300);
            $process->run();

            $this->runLint(true);
            $this->runAnalyze();
        }
    }

    private function runReview(): int
    {
        $this->runLint(false);
        $this->runAnalyze();
        $this->runTestsInternal();

        $this->newLine();
        $this->info('📋 Detailed Output:');
        $this->newLine();

        foreach ($this->results as $name => $result) {
            if (! $result['success'] && isset($result['output'])) {
                $this->warn("--- {$name} ---");
                $this->line($result['output']);
            }
        }

        return self::SUCCESS;
    }

    private function runFixes(): int
    {
        $this->info('🔧 Applying automatic fixes...');
        $this->newLine();

        $this->info('Running Pint...');
        $process = new Process([base_path('vendor/bin/pint')], base_path());
        $process->setTimeout(120);
        $process->run();

        $this->line($process->isSuccessful() ? '  ✓ Code style fixed' : '  ✗ Pint failed');

        $this->info('Running Rector...');
        $process = new Process([
            base_path('vendor/bin/rector'),
            '--memory-limit=512M',
        ], base_path());
        $process->setTimeout(300);
        $process->run();

        $this->line($process->isSuccessful() ? '  ✓ Refactoring applied' : '  ✗ Rector failed');

        $this->newLine();
        $this->info('Verifying fixes...');

        $this->runLint(false);
        $this->runAnalyze();
        $this->runTestsInternal();

        $failed = array_filter($this->results, fn ($r) => ! $r['success']);

        if (empty($failed)) {
            $this->info('✅ All issues fixed!');

            return self::SUCCESS;
        }

        $this->warn('⚠️  Some issues remain - manual intervention may be needed');

        return self::FAILURE;
    }

    private function runTestsOnly(): int
    {
        $this->runTestsInternal();

        return $this->results['test']['success'] ? self::SUCCESS : self::FAILURE;
    }

    private function displaySummary(float $duration): void
    {
        $this->newLine();
        $this->line('  ═══════════════════════════════════════════════════════════');

        $passed = count(array_filter($this->results, fn ($r) => $r['success']));
        $total = count($this->results);

        if ($passed === $total) {
            $this->info("  ✅ ALL CHECKS PASSED ({$duration}s) - {$passed}/{$total}");
        } else {
            $this->error("  ❌ CHECKS FAILED ({$duration}s) - {$passed}/{$total} passed");

            $this->newLine();
            $this->line('  Failed:');
            foreach ($this->results as $name => $result) {
                if (! $result['success']) {
                    $this->line("    • {$name}");
                }
            }

            $this->newLine();
            $this->line('  Run: spotify dev fix to auto-fix');
        }
    }

    private function invalidTask(string $task): int
    {
        $this->error("Unknown task: {$task}");
        $this->newLine();

        $this->line('Available tasks:');
        $this->line('  all     - Run all quality checks (default)');
        $this->line('  fix     - Auto-fix all issues');
        $this->line('  review  - Show detailed review output');
        $this->line('  test    - Run tests only');

        return self::FAILURE;
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
