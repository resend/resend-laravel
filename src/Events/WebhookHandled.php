<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;

class WebhookHandled
{
    use Dispatchable;

    /**
     * Create a new webhook handled event instance.
     */
    public function __construct(
        public array $payload = []
    ) {
        //
    }
}
