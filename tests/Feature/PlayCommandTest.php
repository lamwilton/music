<?php

use App\Services\SpotifyService;

describe('PlayCommand', function () {

    it('searches and plays a track', function () {
        $searchResult = [
            'uri' => 'spotify:track:123',
            'name' => 'Test Song',
            'artist' => 'Test Artist',
        ];

        $this->mock(SpotifyService::class, function ($mock) use ($searchResult) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('search')
                ->once()
                ->with('test song')
                ->andReturn($searchResult);
            $mock->shouldReceive('play')
                ->once()
                ->with('spotify:track:123', null);
        });

        $this->artisan('play', ['query' => 'test song'])
            ->expectsOutput('🎵 Searching for: test song')
            ->expectsOutput('▶️  Playing: Test Song by Test Artist')
            ->expectsOutput('✅ Playback started!')
            ->assertExitCode(0);
    });

    it('handles no search results', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('search')
                ->once()
                ->with('nonexistent song')
                ->andReturn(null);
        });

        $this->artisan('play', ['query' => 'nonexistent song'])
            ->expectsOutput('🎵 Searching for: nonexistent song')
            ->expectsOutput('No results found for: nonexistent song')
            ->assertExitCode(1);
    });

    it('handles API errors gracefully', function () {
        $searchResult = [
            'uri' => 'spotify:track:123',
            'name' => 'Test Song',
            'artist' => 'Test Artist',
        ];

        $this->mock(SpotifyService::class, function ($mock) use ($searchResult) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('search')
                ->once()
                ->with('test song')
                ->andReturn($searchResult);
            $mock->shouldReceive('play')
                ->once()
                ->with('spotify:track:123', null)
                ->andThrow(new Exception('No active device'));
        });

        $this->artisan('play', ['query' => 'test song'])
            ->expectsOutput('🎵 Searching for: test song')
            ->expectsOutput('Failed to play: No active device')
            ->assertExitCode(1);
    });

    it('requires configuration', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $this->artisan('play', ['query' => 'test'])
            ->expectsOutput('❌ Spotify is not configured')
            ->expectsOutputToContain('Run "spotify setup"')
            ->assertExitCode(1);
    });

});
