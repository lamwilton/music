<?php

describe('LoginCommand', function () {

    beforeEach(function () {
        config(['spotify.client_id' => 'test-client-id']);
        config(['spotify.client_secret' => 'test-client-secret']);
        config(['spotify.token_path' => sys_get_temp_dir().'/spotify-test-token.json']);
    });

    afterEach(function () {
        $tokenFile = sys_get_temp_dir().'/spotify-test-token.json';
        if (file_exists($tokenFile)) {
            unlink($tokenFile);
        }
    });

    describe('missing credentials', function () {

        it('fails when client_id is missing', function () {
            config(['spotify.client_id' => null]);
            config(['spotify.client_secret' => 'test-secret']);

            $this->artisan('login')
                ->expectsOutputToContain('Missing Spotify credentials')
                ->assertExitCode(1);
        });

        it('fails when client_secret is missing', function () {
            config(['spotify.client_id' => 'test-id']);
            config(['spotify.client_secret' => null]);

            $this->artisan('login')
                ->expectsOutputToContain('Missing Spotify credentials')
                ->assertExitCode(1);
        });

        it('fails when both credentials are missing', function () {
            config(['spotify.client_id' => null]);
            config(['spotify.client_secret' => null]);

            $this->artisan('login')
                ->expectsOutputToContain('Missing Spotify credentials')
                ->assertExitCode(1);
        });

        it('fails when client_id is empty string', function () {
            config(['spotify.client_id' => '']);
            config(['spotify.client_secret' => 'test-secret']);

            $this->artisan('login')
                ->expectsOutputToContain('Missing Spotify credentials')
                ->assertExitCode(1);
        });

    });

    describe('command metadata', function () {

        it('has correct command name', function () {
            $command = $this->app->make(\App\Commands\LoginCommand::class);
            expect($command->getName())->toBe('login');
        });

        it('has a description', function () {
            $command = $this->app->make(\App\Commands\LoginCommand::class);
            expect($command->getDescription())->not->toBeEmpty();
        });

    });

});
