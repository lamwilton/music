<?php

namespace App\Mcp\Tools;

use App\Services\SpotifyService;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Tool;

#[Name('resume')]
#[Description('Resume Spotify playback from where it was paused')]
class ResumeTool extends Tool
{
    public function handle(Request $request, SpotifyService $spotify): Response
    {
        $spotify->resume();

        return Response::text('Playback resumed.');
    }
}
