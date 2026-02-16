<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class AiCommand extends Command
{
    protected $signature = 'ai {action? : review, explain, optimize, test, doc} {file? : File to process}';

    protected $description = '🤖 AI Agents - Specialized AI agents for code tasks';

    public function handle(): int
    {
        $action = $this->argument('action') ?? 'help';

        return match ($action) {
            'review' => $this->call(CodeReviewAgent::class, ['file' => $this->argument('file')]),
            'explain' => $this->call(ExplainAgent::class, ['file' => $this->argument('file')]),
            'optimize' => $this->call(OptimizeAgent::class, ['file' => $this->argument('file')]),
            'test' => $this->call(TestGenAgent::class, ['file' => $this->argument('file')]),
            'doc' => $this->call(DocAgent::class, ['file' => $this->argument('file')]),
            default => $this->showHelp(),
        };
    }

    private function showHelp(): int
    {
        $this->info('🤖 AI Agents for Spotify CLI');
        $this->newLine();

        $this->line('Usage:');
        $this->line('  spotify ai <action> <file>');
        $this->newLine();

        $this->line('Actions:');
        $this->line('  review     Review code for issues');
        $this->line('  explain    Explain what code does');
        $this->line('  optimize   Suggest performance improvements');
        $this->line('  test       Generate PHPUnit/Pest tests');
        $this->line('  doc        Generate PHPDoc documentation');
        $this->newLine();

        $this->line('Examples:');
        $this->line('  spotify ai review app/Services/SpotifyService.php');
        $this->line('  spotify ai explain app/Commands/PlayCommand.php');
        $this->line('  spotify ai test app/Services/SpotifyService.php');

        return self::SUCCESS;
    }
}
