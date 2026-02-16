<?php

use App\Services\SpotifyService;

it('requires configuration', function () {
    $mock = Mockery::mock(SpotifyService::class);
    $mock->shouldReceive('isConfigured')->andReturn(false);
    $this->app->instance(SpotifyService::class, $mock);

    $this->artisan('serve')
        ->assertFailed();
});

it('has correct signature options', function () {
    $command = $this->app->make(\App\Commands\ServeCommand::class);
    $definition = $command->getDefinition();

    expect($definition->hasOption('port'))->toBeTrue();
    expect($definition->hasOption('host'))->toBeTrue();
    expect($definition->getOption('port')->getDefault())->toBe('9876');
    expect($definition->getOption('host')->getDefault())->toBe('127.0.0.1');
});
