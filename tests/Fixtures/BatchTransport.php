<?php

namespace Illuminate\Mail\Transport;

use Symfony\Component\Mime\Email;

/**
 * Stand-in for the contract added in laravel/framework so the batch transport can be
 * tested against Laravel versions that don't ship it yet.
 */
interface BatchTransport
{
    public function maxBatchSize(): int;

    public function canBatch(Email $email): bool;

    public function sendBatch(array $messages): array;
}
