<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailComplained
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email complained event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
