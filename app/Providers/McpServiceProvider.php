<?php

namespace App\Providers;

use App\Mcp\SpotifyServer;
use Illuminate\Support\ServiceProvider;
use Laravel\Mcp\Console\Commands\StartCommand;
use Laravel\Mcp\Request;
use Laravel\Mcp\Server\Registrar;

class McpServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Registrar::class, fn (): Registrar => new Registrar);
    }

    public function boot(): void
    {
        $registrar = $this->app->make(Registrar::class);
        $registrar->local('spotify', SpotifyServer::class);

        $this->app->resolving(Request::class, function (Request $request, $app): void {
            if ($app->bound('mcp.request')) {
                $currentRequest = $app->make('mcp.request');
                $request->setArguments($currentRequest->all());
                $request->setSessionId($currentRequest->sessionId());
                $request->setMeta($currentRequest->meta());
            }
        });

        if ($this->app->runningInConsole()) {
            $this->commands([StartCommand::class]);
        }
    }
}
