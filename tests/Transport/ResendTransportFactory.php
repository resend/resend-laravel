<?php

use Illuminate\Config\Repository;
use Illuminate\Mail\MailManager;
use Resend\Client;
use Resend\Laravel\ResendServiceProvider;
use Resend\Laravel\Transport\ResendTransportFactory;
use Resend\Responses\Email\Sent;
use Symfony\Component\Mime\Email;

test('get transport', function () {
    $app = app();

    $app->bind('config', fn () => new Repository([
        'resend' => [
            'api_key' => 'test',
        ],
    ]));

    $app->singleton('mail.manager', function ($app) {
        return new MailManager($app);
    });

    $provider = new ResendServiceProvider($app);
    $provider->register();
    $provider->boot();

    $manager = $app->get('mail.manager');

    $transport = $manager->createSymfonyTransport(['transport' => 'resend']);

    expect((string) $transport)->toBe('resend');
});

test('send', function () {
    $message = (new Email())
        ->subject('Foo Subject')
        ->text('Bar body')
        ->sender('myself@example.com')
        ->to('me@example.com')
        ->bcc('you@example.com');

    $resendResult = new Sent('id', 'myself@example.com', 'me@example.com', '2022-04-01');

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

test('can handle exceptions', function () {
    $message = (new Email())
        ->subject('Foo Subject')
        ->text('Bar body')
        ->sender('myself@example.com')
        ->bcc('you@example.com');

    $client = mock(Client::class)->shouldReceive('sendEmail')
        ->once()
        ->andThrow(Exception::class)
        ->getMock();

    (new ResendTransportFactory($client))->send($message);
})->throws(Exception::class);
