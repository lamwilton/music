<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

class DocAgent extends Command
{
    protected $signature = 'ai:doc {file? : File to document}';

    protected $description = '🤖 AI Documentation Agent - Generates PHPDoc and documentation';

    public function handle(): int
    {
        $file = $this->argument('file');

        if (! $file) {
            $file = $this->ask('Which file to document?');
        }

        $fullPath = base_path($file);

        if (! file_exists($fullPath)) {
            $this->error("File not found: {$file}");

            return self::FAILURE;
        }

        $this->info("Generating documentation for {$file}...");
        $this->newLine();

        $content = file_get_contents($fullPath);

        $prompt = 'Generate PHPDoc comments and inline documentation for this PHP code. '.
                   'Include @param, @return, @throws, and @see annotations where appropriate. '.
                   "Also provide a brief class/method summary.\n\n```php\n{$content}\n```";

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
