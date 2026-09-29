<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TopicCreated
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new topic created event instance.
     */
    public function __construct(
        public array $payload,
        public array $headers = []
    ) {
        //
    }
}
