<?php

use App\Services\SpotifyService;

describe('QueueCommand', function () {

    it('adds track to queue', function () {
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
            $mock->shouldReceive('addToQueue')
                ->once()
                ->with('spotify:track:123');
        });

        $this->artisan('queue', ['query' => 'test song'])
            ->expectsOutput('🎵 Searching for: test song')
            ->expectsOutput('➕ Added to queue: Test Song by Test Artist')
            ->expectsOutput('📋 It will play after the current track')
            ->expectsOutput('✅ Successfully added to queue!')
            ->assertExitCode(0);
    });

    it('handles no search results', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('search')
                ->once()
                ->with('nonexistent')
                ->andReturn(null);
        });

        $this->artisan('queue', ['query' => 'nonexistent'])
            ->expectsOutput('🎵 Searching for: nonexistent')
            ->expectsOutput('No results found for: nonexistent')
            ->assertExitCode(1);
    });

    it('handles API errors', function () {
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
            $mock->shouldReceive('addToQueue')
                ->once()
                ->with('spotify:track:123')
                ->andThrow(new Exception('No active device'));
        });

        $this->artisan('queue', ['query' => 'test song'])
            ->expectsOutput('🎵 Searching for: test song')
            ->expectsOutput('Failed to add to queue: No active device')
            ->assertExitCode(1);
    });

    it('requires configuration', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $this->artisan('queue', ['query' => 'test'])
            ->expectsOutput('❌ Spotify is not configured')
            ->expectsOutput('💡 Run "spotify setup" to configure Spotify')
            ->assertExitCode(1);
    });

});
