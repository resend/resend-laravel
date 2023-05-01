<?php

use Illuminate\Mail\MailManager;
use Mockery as Mockery;
use Resend\Contracts\Client;
use Resend\Email;
use Resend\Laravel\Transport\ResendTransportFactory;
use Resend\Service\Email as EmailService;
use Symfony\Component\Mime\Email as SymfonyEmail;

beforeEach(function () {
    $this->client = Mockery::mock(Client::class);
    $this->client->emails = Mockery::mock(EmailService::class);
    $this->transporter = new ResendTransportFactory($this->client);
});

test('get transport', function () {
    $app = app();

    $app['config']->set('resend', [
        'api_key' => 're_test_12345678',
    ]);

    $manager = $app->get(MailManager::class);

    $transport = $manager->createSymfonyTransport(['transport' => 'resend']);

    expect((string) $transport)->toBe('resend');
});

test('constructor', function () {
    expect($this->transporter)->toBeInstanceOf(ResendTransportFactory::class);
});

test('send', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to('to@example.com')
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('reply-to@example.com')
        ->subject('Test Subject')
        ->text('Test plain text body')
        ->html('<p>Test HTML body</p>');

    $apiResponse = new Email([]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === 'to@example.com' &&
                $arg['bcc'] === 'bcc@example.com';
        }))
        ->andReturn($apiResponse);

    $this->transporter->send($email);
});

test('can handle exceptions', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to('to@example.com')
        ->subject('Test Subject')
        ->text('Test plain text body')
        ->html('<p>Test HTML body</p>');

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->andThrow(Exception::class);

    $this->transporter->send($email);
})->throws(Exception::class);
