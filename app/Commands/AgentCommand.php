<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class AgentCommand extends Command
{
    protected $signature = 'agent {action? : lint, analyze, refactor, test, quality-gate} {--fix : Apply automatic fixes}';

    protected $description = '🤖 Quality agents - Run code quality checks';

    public function handle(): int
    {
        $action = $this->argument('action') ?? 'quality-gate';
        $fix = $this->option('fix');

        return match ($action) {
            'lint' => $this->call(LintAgent::class, ['--fix' => $fix]),
            'analyze' => $this->call(AnalyzeAgent::class),
            'refactor' => $this->call(RefactorAgent::class, ['--dry-run' => ! $fix]),
            'test' => $this->call(TestAgent::class, ['--coverage' => $fix]),
            'quality-gate' => $this->call(QualityGateAgent::class, ['--fix' => $fix]),
            default => $this->invalidAgent($action),
        };
    }

    private function invalidAgent(string $action): int
    {
        $this->error("Unknown agent: {$action}");
        $this->newLine();
        $this->line('Available agents:');
        $this->line('  lint          - Run code linting');
        $this->line('  analyze       - Run static analysis');
        $this->line('  refactor      - Run automated refactoring');
        $this->line('  test          - Run tests');
        $this->line('  quality-gate  - Run all checks');

        return self::FAILURE;
    }
}
