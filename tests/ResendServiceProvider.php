<?php

use Illuminate\Config\Repository;
use Resend\Client;
use Resend\Laravel\ResendServiceProvider;

it('requires an API key', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([]));

    (new ResendServiceProvider($app))->register();
})->throws(InvalidArgumentException::class);

it('provides', function () {
    $app = app();

    $provides = (new ResendServiceProvider($app))->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
