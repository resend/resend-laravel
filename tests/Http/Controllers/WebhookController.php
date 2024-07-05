<?php

use Illuminate\Support\Facades\Event;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailDeliveryDelayed;
use Resend\Laravel\Events\EmailOpened;
use Resend\Laravel\Events\EmailSent;
use Resend\Laravel\Http\Controllers\WebhookController as Controller;

test('correct methods are called and handled based on resend webhook event', function (string $name, string $event) {
    $request = webhookRequest($name);

    Event::fake([
        $event,
    ]);

    $response = (new Controller)->handleWebhook($request);

    Event::assertDispatched($event, function ($e) use ($request) {
        return $request->getContent() == json_encode($e->payload);
    });

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('Webhook handled');
})->with([
    ['email.bounced', EmailBounced::class],
    ['email.clicked', EmailClicked::class],
    ['email.complained', EmailComplained::class],
    ['email.delivered', EmailDelivered::class],
    ['email.delivery_delayed', EmailDeliveryDelayed::class],
    ['email.opened', EmailOpened::class],
    ['email.sent', EmailSent::class],
]);

test('normal response is returned if method is missing', function () {
    $request = webhookRequest('email.foo');

    Event::fake();

    $response = (new Controller)->handleWebhook($request);

    Event::assertNothingDispatched();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBeEmpty();
});

test('verify webhook signature middleware is called when webhook secret is set', function () {
    config(['resend.webhook.secret' => 'secret']);
    config(['resend.webhook.tolerance' => 300]);

    Event::fake([
        EmailDelivered::class,
    ]);

    $this->postJson('/resend/webhook', [
        'id' => 're_evt_123456789',
        'type' => 'email.delivered',
    ])->assertForbidden();

    Event::assertNothingDispatched();
});

// This function creates a new Request object with a JSON body that does not include the fields expected by your controller.
function invalidWebhookRequest() {
    return new Illuminate\Http\Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['invalid' => 'data']));
}

test('it returns an error response for invalid payload', function() {
    $request = invalidWebhookRequest();

    $response = (new Controller)->handleWebhook($request);

    expect($response->getStatusCode())->toBe(400);
});
