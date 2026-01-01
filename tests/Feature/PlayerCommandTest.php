<?php

use App\Services\SpotifyService;

describe('PlayerCommand', function () {

    it('requires configuration', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $this->artisan('player')
            ->expectsOutput('❌ Spotify is not configured')
            ->expectsOutput('💡 Run "spotify setup" first')
            ->assertExitCode(1);
    });

    it('requires interactive terminal', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
        });

        $this->artisan('player', ['--no-interaction' => true])
            ->expectsOutput('❌ Player requires an interactive terminal')
            ->expectsOutput('💡 Run without piping or in a proper terminal')
            ->assertExitCode(1);
    });

    it('shows nothing playing state', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
        });

        $this->artisan('player')
            ->expectsOutput('🎵 Spotify Interactive Player')
            ->expectsOutput('Loading...');
    });

    it('displays current track info', function () {
        $currentTrack = [
            'name' => 'Test Song',
            'artist' => 'Test Artist',
            'album' => 'Test Album',
            'progress_ms' => 90000,
            'duration_ms' => 180000,
            'is_playing' => true,
            'device' => [
                'volume_percent' => 50,
            ],
        ];

        $this->mock(SpotifyService::class, function ($mock) use ($currentTrack) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn($currentTrack);
        });

        $this->artisan('player')
            ->expectsOutput('🎵 Spotify Interactive Player')
            ->expectsOutput('Loading...');
    });

});
