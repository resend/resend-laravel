<?php

use Illuminate\Mail\MailManager;
use Mockery as Mockery;
use Resend\Collection;
use Resend\Contracts\Client;
use Resend\Email;
use Resend\Laravel\Transport\ResendBatchTransport;
use Resend\Service\Batch as BatchService;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email as SymfonyEmail;

beforeEach(function () {
    $this->client = Mockery::mock(Client::class);
    $this->client->batch = Mockery::mock(BatchService::class);
    $this->transporter = new ResendBatchTransport($this->client);
});

function batchMessage(string $to, string $subject): SentMessage
{
    $email = (new SymfonyEmail())
        ->from('from@example.com')
        ->to($to)
        ->subject($subject)
        ->html("<p>{$subject}</p>");

    return new SentMessage($email, Envelope::create($email));
}

it('is used as the resend transport when laravel supports batching', function () {
    app()['config']->set('resend', ['api_key' => 're_test_12345678']);

    $transport = app(MailManager::class)->createSymfonyTransport(['transport' => 'resend']);

    expect($transport)->toBeInstanceOf(ResendBatchTransport::class);
});

it('only batches emails without attachments or idempotency keys', function () {
    $email = (new SymfonyEmail())->from('from@example.com')->to('to@example.com')->text('Hi');

    expect($this->transporter->maxBatchSize())->toBe(100)
        ->and($this->transporter->canBatch($email))->toBeTrue()
        ->and($this->transporter->canBatch((clone $email)->attach('content', 'file.txt')))->toBeFalse();

    $email->getHeaders()->addTextHeader('Resend-Idempotency-Key', 'welcome-user/123');

    expect($this->transporter->canBatch($email))->toBeFalse();
});

it('can send a batch', function () {
    $messages = [3 => batchMessage('one@example.com', 'One'), 7 => batchMessage('two@example.com', 'Two')];

    $messages[7]->getOriginalMessage()->getHeaders()->add(new MetadataHeader('category', 'digest'));

    $this->client->batch
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(function ($payloads) {
            return count($payloads) === 2 &&
                $payloads[0]['to'] === ['one@example.com'] &&
                $payloads[1]['subject'] === 'Two' &&
                $payloads[1]['tags'] === [['name' => 'category', 'value' => 'digest']] &&
                ! array_key_exists('attachments', $payloads[0]);
        }), ['batch_validation' => 'permissive'])
        ->andReturn(Collection::from([
            'data' => [Email::from(['id' => 'id-one']), Email::from(['id' => 'id-two'])],
        ]));

    expect($this->transporter->sendBatch($messages))->toBe([])
        ->and($messages[3]->getMessageId())->toBe('id-one')
        ->and($messages[7]->getMessageId())->toBe('id-two')
        ->and($messages[7]->getOriginalMessage()->getHeaders()->get('X-Resend-Email-ID')->getBodyAsString())->toBe('id-two');
});

it('maps permissive validation errors to their messages', function () {
    $messages = [
        batchMessage('one@example.com', 'One'),
        batchMessage('two@example.com', 'Two'),
        batchMessage('three@example.com', 'Three'),
    ];

    $this->client->batch
        ->shouldReceive('send')
        ->once()
        ->andReturn(Collection::from([
            'data' => [Email::from(['id' => 'id-one']), Email::from(['id' => 'id-three'])],
            'errors' => [['index' => 1, 'message' => 'Invalid `to` field']],
        ]));

    $failures = $this->transporter->sendBatch($messages);

    expect(array_keys($failures))->toBe([1])
        ->and($failures[1])->toBeInstanceOf(TransportException::class)
        ->and($failures[1]->getMessage())->toContain('Invalid `to` field')
        ->and($messages[0]->getMessageId())->toBe('id-one')
        ->and($messages[2]->getMessageId())->toBe('id-three');
});

it('uses the configured validation mode', function () {
    $transporter = new ResendBatchTransport($this->client, ['batch_validation' => 'strict']);

    $this->client->batch
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type('array'), ['batch_validation' => 'strict'])
        ->andThrow(new Exception('Validation failed'));

    $transporter->sendBatch([batchMessage('one@example.com', 'One'), batchMessage('two@example.com', 'Two')]);
})->throws(TransportException::class, 'Request to the Resend API failed. Reason: Validation failed');
