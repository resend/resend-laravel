<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailBounced
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email bounced event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
