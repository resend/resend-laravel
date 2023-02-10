<?php

use Illuminate\Config\Repository;
use Illuminate\Mail\MailManager;
use Resend\Client;
use Resend\Laravel\Transport\ResendTransportFactory;
use Resend\Responses\Email\EmailSent;
use Symfony\Component\Mime\Email;

test('get transport', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'resend' => [
            'api_key' => 'test',
        ],
    ]));

    $manager = new MailManager($app);
});

test('send', function () {
    $message = (new Email())
        ->subject('Foo Subject')
        ->text('Bar body')
        ->sender('myself@example.com')
        ->to('me@example.com')
        ->bcc('you@example.com');

    $resendResult = new EmailSent('id', 'myself@example.com', 'me@example.com');

    $client = mock(Client::class)->shouldReceive('sendEmail')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'myself@example.com' &&
                $arg['to'] === 'me@example.com' &&
                $arg['bcc'] === 'you@example.com';
        }))->andReturn($resendResult)
        ->getMock();

    (new ResendTransportFactory($client))->send($message);
});
