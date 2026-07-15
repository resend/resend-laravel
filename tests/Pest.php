<?php

use Illuminate\Http\Request;
use Resend\Laravel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function webhookRequest(string $event): Request
{
    $request = Request::create('/', 'POST', [], [], [], [], json_encode([
        'id' => 're_evt_123456789',
        'type' => $event,
    ]));

    $request->headers->set('svix-id', 'msg_123456789');
    $request->headers->set('svix-timestamp', '1614265330');
    $request->headers->set('svix-signature', 'v1,exampleSignature');

    return $request;
}
