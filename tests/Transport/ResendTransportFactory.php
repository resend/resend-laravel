<?php

use Illuminate\Mail\MailManager;
use Mockery as Mockery;
use Resend\Contracts\Client;
use Resend\Email;
use Resend\Laravel\Transport\ResendTransportFactory;
use Resend\Service\Email as EmailService;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email as SymfonyEmail;

beforeEach(function () {
    $this->client = Mockery::mock(Client::class);
    $this->client->emails = Mockery::mock(EmailService::class);
    $this->transporter = new ResendTransportFactory($this->client);
});

it('can get transport', function () {
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

it('can send', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to(new Address('to@example.com', 'Acme'))
        ->cc('cc@example.com')
        ->bcc('bcc@example.com')
        ->replyTo('reply-to@example.com')
        ->subject('Test Subject')
        ->text('Test plain text body')
        ->html('<p>Test HTML body</p>');

    $apiResponse = new Email([
        'id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794',
        'from' => 'from@example.com',
        'to' => 'to@example.com',
        'created_at' => '2022-07-25T00:28:32.493138+00:00',
    ]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === ['"Acme" <to@example.com>'] &&
                $arg['cc'] === ['cc@example.com'] &&
                $arg['bcc'] === ['bcc@example.com'] &&
                $arg['reply_to'] === ['reply-to@example.com'];
        }))
        ->andReturn($apiResponse);

    $this->transporter->send($email);
});

it('can send to multiple recipients', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to(new Address('to@example.com', 'Acme'), new Address('sales@example.com', 'Acme Sales'))
        ->subject('Test Subject')
        ->text('Test plain text body')
        ->html('<p>Test HTML body</p>');

    $apiResponse = new Email([
        'id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794',
        'from' => 'from@example.com',
        'to' => 'to@example.com',
        'created_at' => '2022-07-25T00:28:32.493138+00:00',
    ]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === ['"Acme" <to@example.com>', '"Acme Sales" <sales@example.com>'];
        }))
        ->andReturn($apiResponse);

    $this->transporter->send($email);
});

it('can send headers', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to(new Address('to@example.com', 'Acme'))
        ->subject('Test Subject')
        ->text('Test plain text body');
    $email->getHeaders()->addHeader('X-Entity-Ref-ID', '123456789');

    $apiResponse = new Email([
        'id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794',
    ]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === ['"Acme" <to@example.com>'] &&
                $arg['subject'] === 'Test Subject' &&
                array_key_exists('X-Entity-Ref-ID', $arg['headers']);
        }))
        ->andReturn($apiResponse);

    $this->transporter->send($email);
});

it('can send attachments', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to(new Address('to@example.com', 'Acme'))
        ->subject('Test Subject')
        ->text('Test plain text body');

    $email->attach(
        'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean nunc augue, consectetur id neque eget, varius dignissim diam.',
        'lorem-ipsum.txt',
        'text/plain'
    );

    $apiResponse = new Email([
        'id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794',
    ]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === ['"Acme" <to@example.com>'] &&
                $arg['subject'] === 'Test Subject' &&
                ! empty($arg['attachments']) &&
                array_key_exists('filename', $arg['attachments'][0]) &&
                array_key_exists('content', $arg['attachments'][0]) &&
                array_key_exists('content_type', $arg['attachments'][0]) &&
                $arg['attachments'][0]['filename'] === 'lorem-ipsum.txt' &&
                $arg['attachments'][0]['content'] === 'TG9yZW0gaXBzdW0gZG9sb3Igc2l0IGFtZXQsIGNvbnNlY3RldHVyIGFkaXBpc2NpbmcgZWxpdC4gQWVuZWFuIG51bmMgYXVndWUsIGNvbnNlY3RldHVyIGlkIG5lcXVlIGVnZXQsIHZhcml1cyBkaWduaXNzaW0gZGlhbS4=' &&
                $arg['attachments'][0]['content_type'] === 'text/plain';
        }))
        ->andReturn($apiResponse);

    $this->transporter->send($email);
});

it('can handle exceptions', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to('to@example.com')
        ->subject('Test Subject')
        ->text('Test plain text body')
        ->html('<p>Test HTML body</p>');

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->andThrow(Exception::class, 'Failed');

    $this->transporter->send($email);
})->throws(TransportException::class, 'Request to the Resend API failed. Reason: Failed');

it('can set the X-Resend-Email-ID', function () {
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to(new Address('to@example.com', 'Acme'))
        ->subject('Test Subject')
        ->text('Test plain text body');

    $apiResponse = new Email([
        'id' => '49a3999c-0ce1-4ea6-ab68-afcd6dc2e794',
    ]);

    $this->client->emails
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($arg) {
            return $arg['from'] === 'from@example.com' &&
                $arg['to'] === ['"Acme" <to@example.com>'] &&
                $arg['subject'] === 'Test Subject';
        }))
        ->andReturn($apiResponse);

    $message = $this->transporter->send($email);

    // Test the header is set for each message.
    expect($message->getOriginalMessage()
        ->getHeaders()
        ->has('X-Resend-Email-ID')
    )->toBeTrue();

    // Test the header value is correct
    expect($message->getOriginalMessage()
        ->getHeaders()
        ->get('X-Resend-Email-ID')
        ->getValue()
    )->toBe('49a3999c-0ce1-4ea6-ab68-afcd6dc2e794');
});
