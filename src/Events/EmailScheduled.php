<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailScheduled
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email scheduled event instance.
     */
    public function __construct(
        public array $payload,
        public array $headers = []
    ) {
        //
    }
}
