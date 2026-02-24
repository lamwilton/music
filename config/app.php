<?php

return [
    'name' => 'Spotify',
    'version' => 'v0.0.1',
    'env' => 'production',
    'providers' => [
        App\Providers\AppServiceProvider::class,
        App\Providers\McpServiceProvider::class,
    ],
];
