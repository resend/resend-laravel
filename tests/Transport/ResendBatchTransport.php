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

it('fails every accepted message when the response cannot be matched', function () {
    $this->client->batch->shouldReceive('send')->once()->andReturn(Collection::from([
        'data' => [Email::from(['id' => 'id-one'])],
        'errors' => [['index' => 2, 'message' => 'Invalid `to` field']],
    ]));

    $failures = $this->transporter->sendBatch([
        batchMessage('one@example.com', 'One'),
        batchMessage('two@example.com', 'Two'),
        batchMessage('three@example.com', 'Three'),
    ]);

    expect(array_keys($failures))->toBe([0, 1, 2])
        ->and($failures[0]->getMessage())->toContain('Unable to match')
        ->and($failures[2]->getMessage())->toContain('Invalid `to` field');
});

it('fails every message when an error index is invalid', function () {
    $this->client->batch->shouldReceive('send')->once()->andReturn(Collection::from([
        'data' => [Email::from(['id' => 'id-one'])],
        'errors' => [['index' => 'nope', 'message' => 'Invalid']],
    ]));

    $failures = $this->transporter->sendBatch([batchMessage('one@example.com', 'One'), batchMessage('two@example.com', 'Two')]);

    expect(array_keys($failures))->toBe([0, 1]);
});

it('fails messages resend returned no id for', function () {
    $this->client->batch->shouldReceive('send')->once()->andReturn(Collection::from([
        'data' => [Email::from(['id' => 'id-one']), Email::from(['id' => ''])],
    ]));

    $messages = [batchMessage('one@example.com', 'One'), batchMessage('two@example.com', 'Two')];

    $failures = $this->transporter->sendBatch($messages);

    expect(array_keys($failures))->toBe([1])
        ->and($messages[0]->getMessageId())->toBe('id-one');
});

it('does not send a stale X-Resend-Email-ID and replaces it', function () {
    $messages = [batchMessage('one@example.com', 'One'), batchMessage('two@example.com', 'Two')];
    $messages[0]->getOriginalMessage()->getHeaders()->addTextHeader('X-Resend-Email-ID', 'stale');

    $this->client->batch
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::on(fn ($payloads) => ! array_key_exists('X-Resend-Email-ID', $payloads[0]['headers'])), Mockery::any())
        ->andReturn(Collection::from(['data' => [Email::from(['id' => 'id-one']), Email::from(['id' => 'id-two'])]]));

    $this->transporter->sendBatch($messages);

    $ids = array_map(
        fn ($header) => $header->getBodyAsString(),
        iterator_to_array($messages[0]->getOriginalMessage()->getHeaders()->all('X-Resend-Email-ID'), false)
    );

    expect($ids)->toBe(['id-one']);
});

it('reads the validation mode from the mailer config', function (array $config, string $expected) {
    app()['config']->set('resend', ['api_key' => 're_test_12345678']);
    app()->instance('resend', $this->client);

    $this->client->batch
        ->shouldReceive('send')
        ->once()
        ->with(Mockery::type('array'), ['batch_validation' => $expected])
        ->andReturn(Collection::from(['data' => [Email::from(['id' => 'a']), Email::from(['id' => 'b'])]]));

    app(MailManager::class)
        ->createSymfonyTransport(['transport' => 'resend', ...$config])
        ->sendBatch([batchMessage('one@example.com', 'One'), batchMessage('two@example.com', 'Two')]);
})->with([
    'default' => [[], 'permissive'],
    'top level' => [['batch_validation' => 'strict'], 'strict'],
    'options win' => [['batch_validation' => 'strict', 'options' => ['batch_validation' => 'permissive']], 'permissive'],
]);

it('falls back to the single send transport when laravel does not support batching', function () {
    $output = json_decode(shell_exec(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(__DIR__ . '/../Fixtures/legacy-transport.php.stub')), true);

    if ($output['interface']) {
        $this->markTestSkipped('The installed Laravel version supports batching.');
    }

    expect($output['transport'])->toBe(Resend\Laravel\Transport\ResendTransportFactory::class);
});
