<?php

use App\Services\SpotifyService;

describe('ResumeCommand', function () {

    it('resumes playback without device specified', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('resume')->once();
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'album' => 'Test Album',
            ]);
        });

        $this->artisan('resume')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('🎵 Resumed: Test Song by Test Artist')
            ->expectsOutput('✅ Playback resumed!')
            ->assertExitCode(0);
    });

    it('resumes playback on specified device by name', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
                ['id' => 'device-456', 'name' => 'Kitchen Speaker'],
            ]);
            $mock->shouldReceive('transferPlayback')
                ->once()
                ->with('device-123', true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'album' => 'Test Album',
            ]);
        });

        $this->artisan('resume', ['--device' => 'Living Room'])
            ->expectsOutput('🔊 Using device: Living Room Speaker')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('🎵 Resumed: Test Song by Test Artist')
            ->expectsOutput('✅ Playback resumed!')
            ->assertExitCode(0);
    });

    it('resumes playback on specified device by ID', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
                ['id' => 'device-456', 'name' => 'Kitchen Speaker'],
            ]);
            $mock->shouldReceive('transferPlayback')
                ->once()
                ->with('device-456', true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'album' => 'Test Album',
            ]);
        });

        $this->artisan('resume', ['--device' => 'device-456'])
            ->expectsOutput('🔊 Using device: Kitchen Speaker')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('🎵 Resumed: Test Song by Test Artist')
            ->expectsOutput('✅ Playback resumed!')
            ->assertExitCode(0);
    });

    it('fails when specified device is not found', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
            ]);
        });

        $this->artisan('resume', ['--device' => 'Nonexistent Device'])
            ->expectsOutput("❌ Device 'Nonexistent Device' not found")
            ->assertExitCode(1);
    });

    it('requires configuration', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(false);
        });

        $this->artisan('resume')
            ->expectsOutputToContain('Spotify is not configured')
            ->expectsOutputToContain('Run "spotify setup"')
            ->assertExitCode(1);
    });

    it('handles API errors', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('resume')
                ->once()
                ->andThrow(new Exception('No active device'));
        });

        $this->artisan('resume')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('Failed to resume: No active device')
            ->assertExitCode(1);
    });

    it('handles transfer playback errors when device specified', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
            ]);
            $mock->shouldReceive('transferPlayback')
                ->once()
                ->andThrow(new Exception('Device not responding'));
        });

        $this->artisan('resume', ['--device' => 'Living Room'])
            ->expectsOutput('🔊 Using device: Living Room Speaker')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('Failed to resume: Device not responding')
            ->assertExitCode(1);
    });

    it('resumes without current track info', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('resume')->once();
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
        });

        $this->artisan('resume')
            ->expectsOutput('▶️  Resuming Spotify playback...')
            ->expectsOutput('✅ Playback resumed!')
            ->assertExitCode(0);
    });

    describe('JSON output mode', function () {

        it('outputs JSON on successful resume without device', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('resume')->once();
                $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                    'name' => 'Test Song',
                    'artist' => 'Test Artist',
                    'album' => 'Test Album',
                ]);
            });

            $this->artisan('resume', ['--json' => true])
                ->expectsOutput(json_encode([
                    'success' => true,
                    'resumed' => true,
                    'device_id' => null,
                    'track' => [
                        'name' => 'Test Song',
                        'artist' => 'Test Artist',
                        'album' => 'Test Album',
                    ],
                ]))
                ->assertExitCode(0);
        });

        it('outputs JSON on successful resume with device', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getDevices')->once()->andReturn([
                    ['id' => 'device-123', 'name' => 'Living Room Speaker'],
                ]);
                $mock->shouldReceive('transferPlayback')
                    ->once()
                    ->with('device-123', true);
                $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                    'name' => 'Test Song',
                    'artist' => 'Test Artist',
                    'album' => 'Test Album',
                ]);
            });

            $this->artisan('resume', ['--device' => 'Living Room', '--json' => true])
                ->expectsOutput('🔊 Using device: Living Room Speaker')
                ->expectsOutput(json_encode([
                    'success' => true,
                    'resumed' => true,
                    'device_id' => 'device-123',
                    'track' => [
                        'name' => 'Test Song',
                        'artist' => 'Test Artist',
                        'album' => 'Test Album',
                    ],
                ]))
                ->assertExitCode(0);
        });

        it('outputs JSON with null track when no current playback', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('resume')->once();
                $mock->shouldReceive('getCurrentPlayback')->once()->andReturn(null);
            });

            $this->artisan('resume', ['--json' => true])
                ->expectsOutput(json_encode([
                    'success' => true,
                    'resumed' => true,
                    'device_id' => null,
                    'track' => null,
                ]))
                ->assertExitCode(0);
        });

        it('outputs JSON error on failure', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('resume')
                    ->once()
                    ->andThrow(new Exception('Player command failed'));
            });

            $this->artisan('resume', ['--json' => true])
                ->expectsOutput(json_encode([
                    'success' => false,
                    'error' => 'Player command failed',
                ]))
                ->assertExitCode(1);
        });

        it('does not output resuming message in JSON mode', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('resume')->once();
                $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                    'name' => 'Test Song',
                    'artist' => 'Test Artist',
                    'album' => 'Test Album',
                ]);
            });

            $this->artisan('resume', ['--json' => true])
                ->doesntExpectOutput('▶️  Resuming Spotify playback...')
                ->doesntExpectOutput('✅ Playback resumed!')
                ->assertExitCode(0);
        });

    });

    it('uses case-insensitive device name matching', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
            ]);
            $mock->shouldReceive('transferPlayback')
                ->once()
                ->with('device-123', true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'album' => 'Test Album',
            ]);
        });

        $this->artisan('resume', ['--device' => 'living room'])
            ->expectsOutput('🔊 Using device: Living Room Speaker')
            ->assertExitCode(0);
    });

    it('matches partial device names', function () {
        $this->mock(SpotifyService::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->once()->andReturn(true);
            $mock->shouldReceive('getDevices')->once()->andReturn([
                ['id' => 'device-123', 'name' => 'Living Room Speaker'],
                ['id' => 'device-456', 'name' => 'Kitchen Speaker'],
            ]);
            $mock->shouldReceive('transferPlayback')
                ->once()
                ->with('device-456', true);
            $mock->shouldReceive('getCurrentPlayback')->once()->andReturn([
                'name' => 'Test Song',
                'artist' => 'Test Artist',
                'album' => 'Test Album',
            ]);
        });

        $this->artisan('resume', ['--device' => 'Kitchen'])
            ->expectsOutput('🔊 Using device: Kitchen Speaker')
            ->assertExitCode(0);
    });

});
