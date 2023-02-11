<?php

use Resend\Client;
use Resend\Laravel\Facades\Resend;

it('resolves Resend client', function () {
    $app = app();

    $app->get('config')->set('resend', [
        'api_key' => 'test',
    ]);

    expect(Resend::getFacadeRoot())->toBeInstanceOf(Client::class);
});
