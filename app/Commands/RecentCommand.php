<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresSpotifyConfig;
use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class RecentCommand extends Command
{
    use RequiresSpotifyConfig;

    protected $signature = 'recent
        {--limit=20 : Number of results}
        {--json : Output as JSON}';

    protected $description = 'Show recently played tracks';

    public function handle()
    {
        if (! $this->ensureConfigured()) {
            return self::FAILURE;
        }

        $spotify = app(SpotifyService::class);
        $limit = (int) $this->option('limit');

        try {
            $tracks = $spotify->getRecentlyPlayed($limit);

            if ($this->option('json')) {
                $this->line(json_encode($tracks));

                return self::SUCCESS;
            }

            $this->info('🕐 Recently Played:');
            $this->newLine();

            if (empty($tracks)) {
                $this->warn('No recently played tracks found.');

                return self::SUCCESS;
            }

            foreach ($tracks as $i => $track) {
                $this->line('  '.($i + 1).". <fg=cyan>{$track['name']}</> by {$track['artist']}");
                if ($track['played_at']) {
                    $time = \Carbon\Carbon::parse($track['played_at'])->diffForHumans();
                    $this->line("     Played {$time}");
                }
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
