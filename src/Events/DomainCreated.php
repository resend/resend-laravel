<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DomainCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new domain created event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
