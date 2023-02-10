<?php

use Resend\Client;

it('resolves Resend client', function () {
    $app = app();

    $app['config']->set('resend', [
        'api_key' => 'test',
    ]);

    $resend = $app->get('resend');

    expect($resend)->toBeInstanceOf(Client::class);
});
