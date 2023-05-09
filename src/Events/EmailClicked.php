<?php

namespace Resend\Laravel\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EmailClicked
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new email clicked event instance.
     */
    public function __construct(
        public array $payload
    ) {
        //
    }
}
