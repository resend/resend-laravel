<?php

use Illuminate\Config\Repository;
use Resend\Client;
use Resend\Laravel\ResendServiceProvider;

it('binds the client on the container', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'resend' => [
            'api_key' => 'test',
        ],
    ]));

    (new ResendServiceProvider($app))->register();

    expect($app->get(Client::class))->toBeInstanceOf(Client::class);
});

it('provides services', function () {
    $app = app();

    $provides = (new ResendServiceProvider($app))->provides();

    expect($provides)->toBe([
        Client::class,
    ]);
});
