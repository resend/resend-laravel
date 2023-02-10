<?php

use Resend\Client;
use Resend\Laravel\ResendServiceProvider;

it('requires an API key', function () {
    app()->get('resend');
})->throws(InvalidArgumentException::class);

it('provides', function () {
    $provider = app()->resolveProvider(ResendServiceProvider::class);

    $provides = $provider->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
