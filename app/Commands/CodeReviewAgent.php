<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class CodeReviewAgent extends Command
{
    protected $signature = 'ai:review {file? : File to review}';

    protected $description = '🤖 AI Code Review Agent - Reviews code for issues and improvements';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! $file) {
            $file = $this->ask('Which file to review?');
        }

        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Reviewing {$file}...");
        $this->newLine();

        $content = file_get_contents($fullPath);

        $prompt = 'Review the following PHP code for bugs, security issues, code style, and improvements. '.
                   "Provide specific suggestions in a structured format.\n\n```php\n{$content}\n```";

        try {
            $response = agent()->prompt($prompt);

            $this->line($response->content);
            $this->newLine();

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('AI Error: '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
