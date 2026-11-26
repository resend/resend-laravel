<?php

use GuzzleHttp\Client as GuzzleClient;
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

it('applies the default HTTP timeouts to the Resend Client', function () {
    config(['resend.api_key' => 're_test']);

    $guzzle = guzzleClientOf(app('resend'));

    expect($guzzle->getConfig('timeout'))->toBe(30.0)
        ->and($guzzle->getConfig('connect_timeout'))->toBe(10.0);
});

it('can configure the HTTP timeouts of the Resend Client', function () {
    config([
        'resend.api_key' => 're_test',
        'resend.timeout' => 5,
        'resend.connect_timeout' => 2,
    ]);

    $guzzle = guzzleClientOf(app('resend'));

    expect($guzzle->getConfig('timeout'))->toBe(5.0)
        ->and($guzzle->getConfig('connect_timeout'))->toBe(2.0);
});

function guzzleClientOf(Client $client): GuzzleClient
{
    $transporter = (new ReflectionProperty($client, 'transporter'))->getValue($client);

    return (new ReflectionProperty($transporter, 'client'))->getValue($transporter);
}

it('provides', function () {
    $provider = app()->resolveProvider(ResendServiceProvider::class);

    $provides = $provider->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
