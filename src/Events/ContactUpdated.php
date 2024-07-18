<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContactUpdated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new contact updated event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
