<?php

use Resend\Client;
use Resend\Laravel\Facades\Resend;
use Resend\Service\ApiKey;
use Resend\Service\Audience;
use Resend\Service\Batch;
use Resend\Service\Broadcast;
use Resend\Service\Contact;
use Resend\Service\ContactProperty;
use Resend\Service\Domain;
use Resend\Service\Email;
use Resend\Service\Segment;
use Resend\Service\Template;
use Resend\Service\Topic;
use Resend\Service\Webhook;

it('resolves Resend client', function () {
    $app = app();

    $app->get('config')->set('resend', [
        'api_key' => 'test',
    ]);

    expect(Resend::getFacadeRoot())->toBeInstanceOf(Client::class);
});

it('can get an API service', function () {
    $app = app();

    $app->get('config')->set('resend', [
        'api_key' => 'test',
    ]);

    expect(Resend::apiKeys())->toBeInstanceOf(ApiKey::class)
        ->and(Resend::audiences())->toBeInstanceOf(Audience::class)
        ->and(Resend::batch())->toBeInstanceOf(Batch::class)
        ->and(Resend::broadcasts())->toBeInstanceOf(Broadcast::class)
        ->and(Resend::contacts())->toBeInstanceOf(Contact::class)
        ->and(Resend::contactProperties())->toBeInstanceOf(ContactProperty::class)
        ->and(Resend::domains())->toBeInstanceOf(Domain::class)
        ->and(Resend::emails())->toBeInstanceOf(Email::class)
        ->and(Resend::segments())->toBeInstanceOf(Segment::class)
        ->and(Resend::templates())->toBeInstanceOf(Template::class)
        ->and(Resend::topics())->toBeInstanceOf(Topic::class)
        ->and(Resend::webhooks())->toBeInstanceOf(Webhook::class);
});
