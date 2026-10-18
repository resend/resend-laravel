<?php

namespace Resend\Laravel\Transport;

use Exception;
use Illuminate\Mail\Transport\BatchTransport;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

/**
 * The Resend transport with support for Laravel's batch sending.
 *
 * Only registered when the application's Laravel version provides the
 * Illuminate\Mail\Transport\BatchTransport contract.
 */
class ResendBatchTransport extends ResendTransportFactory implements BatchTransport
{
    /**
     * Get the maximum number of messages that may be sent in a single request.
     */
    public function maxBatchSize(): int
    {
        return 100;
    }

    /**
     * Determine if the given message may be sent as part of a batch request.
     */
    public function canBatch(Email $email): bool
    {
        // The batch endpoint doesn't support attachments, and idempotency keys apply
        // to the whole request, so emails using either are sent on their own.
        return $email->getAttachments() === []
            && ! $email->getHeaders()->has('Resend-Idempotency-Key');
    }

    /**
     * Send the given messages in a single request.
     *
     * @param  array<int, \Symfony\Component\Mailer\SentMessage>  $messages
     * @return array<int, \Throwable>
     *
     * @throws \Symfony\Component\Mailer\Exception\TransportException
     */
    public function sendBatch(array $messages): array
    {
        $indexes = array_keys($messages);

        $payloads = array_map(function (SentMessage $message) {
            $payload = $this->getPayload(
                MessageConverter::toEmail($message->getOriginalMessage()),
                $message->getEnvelope()
            );

            unset($payload['attachments']);

            return $payload;
        }, array_values($messages));

        try {
            $result = $this->resend->batch->send($payloads, [
                'batch_validation' => $this->config['batch_validation'] ?? 'permissive',
            ]);
        } catch (Exception $exception) {
            throw $this->requestFailed($exception);
        }

        // In permissive mode, Resend reports emails that failed validation by their
        // position in the request and only returns IDs for the emails it accepted.
        $failures = [];

        foreach ((array) ($result->getAttribute('errors') ?? []) as $error) {
            $position = $error['index'] ?? null;

            if (is_int($position) && isset($indexes[$position])) {
                $failures[$indexes[$position]] = new TransportException(
                    sprintf('Resend rejected the email. Reason: %s', $error['message'] ?? 'Unknown error')
                );
            }
        }

        $accepted = array_values(array_diff_key($messages, $failures));

        foreach (array_values((array) ($result->getAttribute('data') ?? [])) as $position => $email) {
            $messageId = is_array($email) ? ($email['id'] ?? null) : $email->id;

            if (isset($accepted[$position]) && is_string($messageId)) {
                $accepted[$position]->setMessageId($messageId);
                $accepted[$position]->getOriginalMessage()->getHeaders()->addHeader('X-Resend-Email-ID', $messageId);
            }
        }

        return $failures;
    }
}
