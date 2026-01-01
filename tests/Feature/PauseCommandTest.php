<?php

use App\Services\SpotifyService;

describe('PauseCommand', function () {

    it('pauses playback', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'progress_ms' => 90000,
            ]);
            $mock->shouldReceive('pause')->once();
        });

        $this->artisan('pause')
            ->expectsOutput('⏸️  Pausing Spotify playback...')
            ->expectsOutput('✅ Playback paused!')
            ->assertExitCode(0);
    });

    it('handles API errors', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
            $mock->shouldReceive('pause')
                ->once()
                ->andThrow(new Exception('Already paused'));
        });

        $this->artisan('pause')
            ->expectsOutput('⏸️  Pausing Spotify playback...')
            ->expectsOutput('❌ Failed to pause: Already paused')
            ->assertExitCode(1);
    });

    it('requires configuration', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $this->artisan('pause')
            ->expectsOutput('❌ Spotify is not configured')
            ->expectsOutput('💡 Set SPOTIFY_CLIENT_ID and SPOTIFY_CLIENT_SECRET env vars')
            ->expectsOutput('💡 Then run "music login" to authenticate')
            ->assertExitCode(1);
    });

});
