<?php

use Illuminate\Config\Repository;
use Resend\Client;
use Resend\Laravel\Facades\Resend;
use Resend\Laravel\ResendServiceProvider;

it('resolves Resend client', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'resend' => [
            'api_key' => 'test',
        ],
    ]));

    (new ResendServiceProvider($app))->register();

    Resend::setFacadeApplication($app);

    expect(Resend::getFacadeRoot())->toBeInstanceOf(Client::class);
});
