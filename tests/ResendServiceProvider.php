<?php

use Resend\Client;
use Resend\Laravel\Exceptions\ApiKeyIsMissing;
use Resend\Laravel\ResendServiceProvider;

it('requires an API key', function () {
    app()->get('resend');
})->throws(ApiKeyIsMissing::class);

it('can bind the Resend Client using the service config', function () {
    $app = app();
    $app->get('config')->set('services', [
        'resend' => [
            'key' => 're_test',
        ],
    ]);

    $resend = $app->get('resend');

    expect($resend)->toBeInstanceOf(Client::class);
});

it('provides', function () {
    $provider = app()->resolveProvider(ResendServiceProvider::class);

    $provides = $provider->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
