<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class OptimizeAgent extends Command
{
    protected $signature = 'ai:optimize {file? : File to optimize}';

    protected $description = '🤖 AI Optimize Agent - Suggests performance improvements';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! $file) {
            $file = $this->ask('Which file to optimize?');
        }

        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Analyzing {$file} for optimizations...");
        $this->newLine();

        $content = file_get_contents($fullPath);

        $prompt = 'Analyze this PHP code for performance optimizations. '.
                   'Look for N+1 queries, unnecessary loops, caching opportunities, and memory issues. '.
                   "Provide specific improved code snippets.\n\n```php\n{$content}\n```";

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
