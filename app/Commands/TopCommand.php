<?php

namespace App\Commands;

use App\Commands\Concerns\RequiresSpotifyConfig;
use App\Services\SpotifyService;
use LaravelZero\Framework\Commands\Command;

class TopCommand extends Command
{
    use RequiresSpotifyConfig;

    protected $signature = 'top
        {--type=tracks : Type to show (tracks or artists)}
        {--range=medium_term : Time range (short_term, medium_term, long_term)}
        {--limit=20 : Number of results}
        {--json : Output as JSON}';

    protected $description = 'Show your top tracks or artists';

    public function handle()
    {
        if (! $this->ensureConfigured()) {
            return self::FAILURE;
        }

        $spotify = app(SpotifyService::class);
        $type = $this->option('type');
        $range = $this->option('range');
        $limit = (int) $this->option('limit');

        try {
            if ($type === 'artists') {
                $items = $spotify->getTopArtists($range, $limit);

                if ($this->option('json')) {
                    $this->line(json_encode($items));

                    return self::SUCCESS;
                }

                $this->info('🎤 Your Top Artists:');
                $this->newLine();

                foreach ($items as $i => $artist) {
                    $genres = implode(', ', array_slice($artist['genres'], 0, 3));
                    $this->line('  '.($i + 1).". <fg=cyan>{$artist['name']}</>");
                    if ($genres) {
                        $this->line("     Genres: {$genres}");
                    }
                }
            } else {
                $items = $spotify->getTopTracks($range, $limit);

                if ($this->option('json')) {
                    $this->line(json_encode($items));

                    return self::SUCCESS;
                }

                $this->info('🎵 Your Top Tracks:');
                $this->newLine();

                foreach ($items as $i => $track) {
                    $this->line('  '.($i + 1).". <fg=cyan>{$track['name']}</> by {$track['artist']}");
                }
            }

            if (empty($items)) {
                $this->warn('No data found for this time range.');
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ '.$e->getMessage());

            return self::FAILURE;
        }
    }
}
