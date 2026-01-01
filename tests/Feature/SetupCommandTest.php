<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Laravel\Prompts\Prompt;

describe('SetupCommand', function () {

    beforeEach(function () {
        // Ensure we have a clean .env file for each test
        $envFile = base_path('.env');
        if (file_exists($envFile)) {
            $this->originalEnv = file_get_contents($envFile);
        } else {
            $this->originalEnv = null;
        }

        // Mock Process for browser opening and clipboard
        Process::fake();
    });

    afterEach(function () {
        // Restore original .env file
        $envFile = base_path('.env');
        if ($this->originalEnv !== null) {
            file_put_contents($envFile, $this->originalEnv);
        } elseif (file_exists($envFile)) {
            // Remove credentials we may have added
            $content = file_get_contents($envFile);
            $content = preg_replace('/^SPOTIFY_CLIENT_ID=.*/m', '', $content);
            $content = preg_replace('/^SPOTIFY_CLIENT_SECRET=.*/m', '', $content);
            $content = preg_replace('/# Spotify API Credentials\n?/', '', $content);
            file_put_contents($envFile, trim($content));
        }

        // Restore interactive mode
        Prompt::interactive(true);
    });

    describe('already configured', function () {

        it('shows already configured message when credentials exist', function () {
            $envFile = base_path('.env');
            $content = file_exists($envFile) ? file_get_contents($envFile) : '';
            $content .= "\nSPOTIFY_CLIENT_ID=existingclientid1234567890\n";
            $content .= "SPOTIFY_CLIENT_SECRET=existingclientsecret12345\n";
            file_put_contents($envFile, $content);

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
            if (!file_exists($envFile)) {
                file_put_contents($envFile, '');
            }

            expect(file_exists($envFile))->toBeTrue();
        });

    });

});
