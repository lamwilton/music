<?php

namespace App\Mcp\Concerns;

use Laravel\Mcp\Response;

trait HandlesAuthErrors
{
    protected function withAuthHandling(callable $callback): Response
    {
        try {
            return $callback();
        } catch (\Exception $e) {
            $message = $e->getMessage();

            if (str_contains($message, 'Not authenticated') || str_contains($message, 'Session expired')) {
                return Response::error('Spotify auth expired. Run `spotify login` to re-authenticate.');
            }

            throw $e;
        }
    }
}
