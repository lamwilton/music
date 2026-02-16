<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class TestGenAgent extends Command
{
    protected $signature = 'ai:test {file? : File to generate tests for}';

    protected $description = '🤖 AI Test Generator - Creates PHPUnit/Pest tests';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! $file) {
            $file = $this->ask('Which file to generate tests for?');
        }

        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Generating tests for {$file}...");
        $this->newLine();

        $content = file_get_contents($fullPath);

        $prompt = 'Generate Pest PHP tests for the following code. '.
                   'Create comprehensive test cases covering happy paths and edge cases. '.
                   "Use the Pest testing framework syntax.\n\n```php\n{$content}\n```";

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
