<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class ExplainAgent extends Command
{
    protected $signature = 'ai:explain {file? : File to explain}';

    protected $description = '🤖 AI Explain Agent - Explains what code does in simple terms';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! $file) {
            $file = $this->ask('Which file to explain?');
        }

        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Explaining {$file}...");
        $this->newLine();

        $content = file_get_contents($fullPath);

        $prompt = 'Explain what this PHP code does in simple terms. '.
                   "Focus on what the code is trying to accomplish, not technical details.\n\n```php\n{$content}\n```";

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
