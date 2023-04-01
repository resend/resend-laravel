<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Resend\Laravel\Events\WebhookHandled;
use Resend\Laravel\Events\WebhookReceived;
use Resend\Laravel\Http\Controllers\WebhookController as Controller;
use Symfony\Component\HttpFoundation\Response;

test('correct methods are called based on resend event', function () {
    $request = Request::create('/', 'POST', [], [], [], [], json_encode([
        'type' => 'email.delivery_delayed',
    ]));

    Event::fake([
        WebhookHandled::class,
        WebhookReceived::class,
    ]);

    $response = (new WebhookControllerStub)->handleWebhook($request);

    Event::assertDispatched(WebhookReceived::class, function (WebhookReceived $event) {
        return true;
    });

    expect($response->getContent())->toBe('Webhook handled');
});

test('normal response is required if method is missing', function () {
});

class WebhookControllerStub extends Controller
{
    public function __construct()
    {
        //
    }

    public function handleEmailDeliveryDelayed(array $payload): Response
    {
        return new Response('Webhook handled', 200);
    }
}
