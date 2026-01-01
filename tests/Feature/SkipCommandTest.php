<?php

use App\Services\SpotifyService;

describe('SkipCommand', function () {

    describe('skip to next track', function () {

        it('skips to next track by default', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getCurrentPlayback')->andReturn([
                    'name' => 'Song',
                    'artist' => 'Artist',
                    'progress_ms' => 45000,
                    'album' => 'Album',
                ]);
                $mock->shouldReceive('next')->once();
            });

            $this->artisan('skip')
                ->assertExitCode(0);
        });

        it('skips to next track with explicit next argument', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getCurrentPlayback')->andReturn([
                    'name' => 'Song',
                    'artist' => 'Artist',
                    'progress_ms' => 30000,
                    'album' => 'Album',
                ]);
                $mock->shouldReceive('next')->once();
            });

            $this->artisan('skip', ['direction' => 'next'])
                ->assertExitCode(0);
        });

    });

    describe('skip to previous track', function () {

        it('skips to previous track with prev argument', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getCurrentPlayback')->andReturn([
                    'name' => 'Song',
                    'artist' => 'Artist',
                    'progress_ms' => 60000,
                    'album' => 'Album',
                ]);
                $mock->shouldReceive('previous')->once();
            });

            $this->artisan('skip', ['direction' => 'prev'])
                ->assertExitCode(0);
        });

        it('skips to previous track with previous argument', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getCurrentPlayback')->andReturn([
                    'name' => 'Song',
                    'artist' => 'Artist',
                    'progress_ms' => 120000,
                    'album' => 'Album',
                ]);
                $mock->shouldReceive('previous')->once();
            });

            $this->artisan('skip', ['direction' => 'previous'])
                ->assertExitCode(0);
        });

    });

    describe('error handling', function () {

        it('requires configuration', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(false);
            });

            $this->artisan('skip')
                ->assertExitCode(1);
        });

        it('handles API errors', function () {
            $this->mock(SpotifyService::class, function ($mock) {
                $mock->shouldReceive('isConfigured')->once()->andReturn(true);
                $mock->shouldReceive('getCurrentPlayback')->andReturn([
                    'name' => 'Song',
                    'artist' => 'Artist',
                ]);
                $mock->shouldReceive('next')->once()->andThrow(new \Exception('API error'));
            });

            $this->artisan('skip')
                ->assertExitCode(1);
        });

    });

    describe('command metadata', function () {

        it('has correct command name', function () {
            $command = $this->app->make(\App\Commands\SkipCommand::class);
            expect($command->getName())->toBe('skip');
        });

        it('has optional direction argument', function () {
            $command = $this->app->make(\App\Commands\SkipCommand::class);
            $definition = $command->getDefinition();
            expect($definition->hasArgument('direction'))->toBeTrue();
        });

        it('has json option', function () {
            $command = $this->app->make(\App\Commands\SkipCommand::class);
            $definition = $command->getDefinition();
            expect($definition->hasOption('json'))->toBeTrue();
        });

    });

});
