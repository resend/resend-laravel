<?php

use Resend\Client;
use Resend\Laravel\Exceptions\ApiKeyIsMissing;
use Resend\Laravel\ResendServiceProvider;

it('requires an API key', function () {
    app()->get('resend');
})->throws(ApiKeyIsMissing::class);

it('provides', function () {
    $provider = app()->resolveProvider(ResendServiceProvider::class);

    $provides = $provider->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
