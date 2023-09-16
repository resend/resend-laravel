<?php

use Illuminate\Http\Request;
use Resend\Laravel\Tests\TestCase;

uses(TestCase::class)->in(__DIR__);

function webhookRequest(string $event): Request
{
    return Request::create('/', 'POST', [], [], [], [], json_encode([
        'id' => 're_evt_123456789',
        'type' => $event,
    ]));
}
