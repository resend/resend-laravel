<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailDeliveryDelayed;
use Resend\Laravel\Events\EmailOpened;
use Resend\Laravel\Events\EmailSent;
use Resend\Laravel\Http\Controllers\WebhookController as Controller;

function webhookRequest(string $event): Request
{
    return Request::create('/', 'POST', [], [], [], [], json_encode([
        'id' => 're_evt_123456789',
        'type' => $event,
    ]));
}

test('correct methods are called and handled based on resend webhook event', function (string $name, string $event) {
    $request = webhookRequest($name);

    Event::fake([
        $event,
    ]);

    $response = (new WebhookControllerStub)->handleWebhook($request);

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

    $response = (new WebhookControllerStub)->handleWebhook($request);

    Event::assertNothingDispatched();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBeEmpty();
});

class WebhookControllerStub extends Controller
{
    public function __construct()
    {
        // Don't call parent contructor.
    }
}
