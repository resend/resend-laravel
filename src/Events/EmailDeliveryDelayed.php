<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailDeliveryDelayed
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new delivery delayed event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
