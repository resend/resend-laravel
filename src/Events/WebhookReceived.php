<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WebhookReceived
{
    use Dispatchable;

    /**
     * Create a new webhook received event instance.
     */
    public function __construct(
        public array $payload = []
    ) {
        //
    }
}
