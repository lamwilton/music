<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresSpotifyConfig;
use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class QueueCommand extends Command
{
    use RequiresSpotifyConfig;

    protected $signature = 'queue {query : Song, artist, or playlist to add to queue}';

    protected $description = 'Add a song to the Spotify queue (plays after current track)';

    public function handle()
    {
        if (! $this->ensureConfigured()) {
            return self::FAILURE;
        }

        $spotify = app(SpotifyService::class);

        $query = $this->argument('query');

        $this->info("🎵 Searching for: {$query}");

        try {
            $result = $spotify->search($query);

            if ($result) {
                // Add to queue
                $spotify->addToQueue($result['uri']);

                $this->info("➕ Added to queue: {$result['name']} by {$result['artist']}");
                $this->info('📋 It will play after the current track');
            } else {
                $this->warn("No results found for: {$query}");

                return self::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Failed to add to queue: '.$e->getMessage());

            return self::FAILURE;
        }

        $this->info('✅ Successfully added to queue!');

        return self::SUCCESS;
    }
}
