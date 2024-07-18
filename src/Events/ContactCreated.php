<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new contact created event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
