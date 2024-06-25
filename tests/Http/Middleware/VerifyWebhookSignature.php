<?php

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Resend\Laravel\Http\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

function withSignature(Request $request, string $secret, int $timestamp): void
{
    $payload = $request->getContent();
    $id = '123';

    $toSign = $id . '.' . $timestamp . '.' . $payload;
    $signature = base64_encode(pack('H*', hash_hmac('sha256', $toSign, base64_decode($secret))));

    $request->headers->set('svix-id', $id);
    $request->headers->set('svix-timestamp', $timestamp);
    $request->headers->set('svix-signature', 'v1,' . $signature);
}

beforeEach(function () {
    config(['resend.webhook.secret' => 'secret']);
    config(['resend.webhook.tolerance' => 300]);
});

it('recieves a response when a signature is verified', function () {
    $request = webhookRequest('email.delivered');
    $timestamp = time();

    withSignature($request, 'secret', $timestamp);

    $response = (new VerifyWebhookSignature())->handle($request, function () {
        return new Response('OK');
    });

    expect($response->getContent())->toBe('OK');
});

it('throws an exception when the secret cannot be verified', function () {
    $request = webhookRequest('email.delivered');
    $timestamp = time();

    withSignature($request, 'secret', $timestamp + 400);

    (new VerifyWebhookSignature())->handle($request, function () {
        return new Response('OK');
    });
})->throws(AccessDeniedHttpException::class, 'Message timestamp too new');

test('middleware rejects request with invalid signature', function () {
    $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode(['data' => 'test']));
    $request->headers->set('Signature', 'invalid_signature');

    $middleware = new VerifyWebhookSignature();

    $this->expectException(AccessDeniedHttpException::class);

    $middleware->handle($request, function ($req) {
        return new Response('OK', 200);
    });
});
