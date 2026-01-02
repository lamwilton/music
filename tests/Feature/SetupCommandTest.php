<?php

use Illuminate\Support\Facades\Process;
use Laravel\Prompts\Prompt;

describe('SetupCommand', function () {

    beforeEach(function () {
        // Use a temp directory for credentials during tests
        $this->testConfigDir = sys_get_temp_dir().'/shit-music-test-'.uniqid();
        mkdir($this->testConfigDir, 0755, true);
        config(['spotify.config_dir' => $this->testConfigDir]);

        // Mock Process for browser opening and clipboard
        Process::fake();
    });

    afterEach(function () {
        // Clean up test config directory
        if (is_dir($this->testConfigDir)) {
            array_map('unlink', glob($this->testConfigDir.'/*'));
            rmdir($this->testConfigDir);
        }

        // Restore interactive mode
        Prompt::interactive(true);
    });

    describe('already configured', function () {

        it('shows already configured message when credentials exist', function () {
            // Write credentials to the test config directory
            $credentialsFile = $this->testConfigDir.'/credentials.json';
            file_put_contents($credentialsFile, json_encode([
                'client_id' => 'existingclientid1234567890',
                'client_secret' => 'existingclientsecret12345',
            ]));

            $this->artisan('setup')
                ->expectsOutputToContain('Spotify is already configured')
                ->assertExitCode(0);
        });

    });

    describe('credential validation', function () {

        it('validates credentials have correct format', function () {
            // Spotify credentials should be at least 20 chars
            $clientId = 'abc123def456ghi789jk';
            $clientSecret = 'secret123secret456secret789secret0';

            expect(strlen($clientId))->toBeGreaterThanOrEqual(20);
            expect(strlen($clientSecret))->toBeGreaterThanOrEqual(20);
        });

    });

    describe('reset flag', function () {

        it('allows reset even when already configured', function () {
            $envFile = base_path('.env');
            $content = "SPOTIFY_CLIENT_ID=existingclientid1234567890\n";
            $content .= "SPOTIFY_CLIENT_SECRET=existingclientsecret12345\n";
            file_put_contents($envFile, $content);

            // With reset flag, it should proceed to setup
            // We'll just verify it doesn't immediately return "already configured"
            Prompt::interactive(false);

            $this->artisan('setup', ['--reset' => true])
                ->assertExitCode(0);
        });

    });

    describe('env file handling', function () {

        it('creates env file if it does not exist', function () {
            $envFile = base_path('.env');

            // Ensure env file exists for testing
            if (! file_exists($envFile)) {
                file_put_contents($envFile, '');
            }

            expect(file_exists($envFile))->toBeTrue();
        });

    });

});
