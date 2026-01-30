<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailReceived
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email received event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
