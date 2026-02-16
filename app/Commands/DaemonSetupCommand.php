<?php

namespace App\Commands;

use LaravelZero\Framework\Commands\Command;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\warning;

class DaemonSetupCommand extends Command
{
    protected $signature = 'daemon:setup';

    protected $description = 'Set up the Spotify daemon with all dependencies';

    public function handle()
    {
        $this->banner();

        info('This will set up the headless Spotify daemon for CLI playback.');
        $this->newLine();

        $this->checkDependencies();

        $this->authenticateSpotifyd();

        $this->startDaemon();

        $this->displaySuccess();
    }

    private function banner(): void
    {
        $this->newLine();
        $this->line('  ╔═══════════════════════════════════════════╗');
        $this->line('  ║     🎵 Spotify Daemon Setup               ║');
        $this->line('  ╚═══════════════════════════════════════════╝');
        $this->newLine();
    }

    private function checkDependencies(): void
    {
        info('📦 Checking dependencies...');
        $this->newLine();

        $issues = [];

        // Check sox
        $sox = trim(shell_exec('which play 2>/dev/null'));
        if (! $sox) {
            warning('❌ sox not found (required for audio playback)');
            $issues[] = 'sox';
        } else {
            info('✅ sox installed');
        }

        // Check spotifyd
        $spotifyd = trim(shell_exec('which spotifyd 2>/dev/null'));
        if (! $spotifyd) {
            warning('❌ spotifyd not found (required for Spotify Connect)');
            $issues[] = 'spotifyd';
        } else {
            info('✅ spotifyd installed');
        }

        if (empty($issues)) {
            return;
        }

        $this->newLine();
        $install = confirm('Install missing dependencies now?', true);

        if (! $install) {
            error('Setup cancelled. Install dependencies manually:');
            info('  macOS: brew install spotifyd sox');
            info('  Linux: apt install spotifyd sox');
            exit(1);
        }

        $this->installDependencies($issues);
    }

    private function installDependencies(array $issues): void
    {
        $os = PHP_OS_FAMILY;

        if ($os === 'Darwin') {
            $cmd = 'brew install '.implode(' ', $issues);
        } elseif ($os === 'Linux') {
            $cmd = 'sudo apt install -y '.implode(' ', $issues);
        } else {
            error("Unsupported OS: {$os}");
            exit(1);
        }

        info("Running: {$cmd}");
        $this->newLine();

        passthru($cmd);

        // Verify
        foreach ($issues as $dep) {
            if ($dep === 'sox') {
                $check = trim(shell_exec('which play 2>/dev/null'));
            } else {
                $check = trim(shell_exec('which '.$dep.' 2>/dev/null'));
            }

            if (! $check) {
                error("❌ Failed to install {$dep}");
                exit(1);
            }
        }

        info('✅ All dependencies installed');
    }

    private function authenticateSpotifyd(): void
    {
        $this->newLine();
        info('🔐 Setting up Spotify authentication...');
        $this->newLine();

        $cachePath = $_SERVER['HOME'].'/.config/spotify-cli/cache';
        if (! is_dir($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        // Check if already authenticated
        $credFile = $cachePath.'/credentials.json';
        if (file_exists($credFile)) {
            info('✅ Already authenticated with Spotify');

            return;
        }

        warning('You will be asked to authenticate with Spotify in your browser.');
        info('After authenticating, return here.');
        $this->newLine();

        $spotifyd = trim(shell_exec('which spotifyd')) ?: '/opt/homebrew/opt/spotifyd/bin/spotifyd';

        passthru("{$spotifyd} authenticate --cache-path {$cachePath}");

        if (file_exists($credFile)) {
            info('✅ Spotify authentication successful!');
        } else {
            error('❌ Authentication failed');
            exit(1);
        }
    }

    private function startDaemon(): void
    {
        $this->newLine();
        info('🚀 Starting Spotify daemon...');
        $this->newLine();

        $this->call('daemon', ['action' => 'start']);
    }

    private function displaySuccess(): void
    {
        $this->newLine();
        $this->line('  ╔═══════════════════════════════════════════╗');
        $this->line('  ║     ✅ Setup Complete!                    ║');
        $this->line('  ╚═══════════════════════════════════════════╝');
        $this->newLine();

        info('Usage:');
        info('  spotify play "song name" --device="Spotify CLI"');
        info('  spotify daemon start');
        info('  spotify daemon stop');
        $this->newLine();
    }
}
