<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailDelivered
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email delivered event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
