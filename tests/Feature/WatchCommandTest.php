<?php

use App\Services\SpotifyService;

it('requires configuration', function () {
    $mock = Mockery::mock(SpotifyService::class);
    $mock->shouldReceive('isConfigured')->andReturn(false);
    $this->app->instance(SpotifyService::class, $mock);

    $this->artisan('watch')
        ->assertFailed();
});

it('has correct signature options', function () {
    $command = $this->app->make(\App\Commands\WatchCommand::class);
    $definition = $command->getDefinition();

    expect($definition->hasOption('interval'))->toBeTrue();
    expect($definition->hasOption('slack'))->toBeTrue();
    expect($definition->hasOption('json'))->toBeTrue();
    expect($definition->getOption('interval')->getDefault())->toBe('10');
});
