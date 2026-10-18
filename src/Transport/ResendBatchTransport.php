<?php

namespace Resend\Laravel\Transport;

use Exception;
use Illuminate\Mail\Transport\BatchTransport;
use Resend\Collection;
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
     * @param  array<int, SentMessage>  $messages
     * @return array<int, \Throwable>
     *
     * @throws TransportException
     */
    public function sendBatch(array $messages): array
    {
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

        return $this->mapBatchResponse($messages, $result);
    }

    /**
     * Map the Resend batch response onto the given messages, returning the failures.
     *
     * In permissive mode, Resend reports emails that failed validation by their position in
     * the request and returns the IDs of the emails it accepted, in order. A response that
     * can't be matched up reliably fails every message, since we can't tell which were sent.
     *
     * @param  array<int, SentMessage>  $messages
     * @return array<int, \Throwable>
     */
    protected function mapBatchResponse(array $messages, Collection $result): array
    {
        $indexes = array_keys($messages);

        $failures = [];

        foreach ((array) ($result->getAttribute('errors') ?? []) as $error) {
            $position = $error['index'] ?? null;

            if (! is_int($position) || ! isset($indexes[$position]) || isset($failures[$indexes[$position]])) {
                return $this->unreadableBatchResponse($messages);
            }

            $failures[$indexes[$position]] = new TransportException(
                sprintf('Resend rejected the email. Reason: %s', $error['message'] ?? 'Unknown error')
            );
        }

        $accepted = array_keys(array_diff_key($messages, $failures));

        $emails = array_values((array) ($result->getAttribute('data') ?? []));

        if (count($emails) !== count($accepted)) {
            $failures += $this->unreadableBatchResponse(array_diff_key($messages, $failures));

            ksort($failures);

            return $failures;
        }

        foreach ($accepted as $position => $index) {
            $messageId = is_array($emails[$position]) ? ($emails[$position]['id'] ?? null) : ($emails[$position]->id ?? null);

            if (! is_string($messageId) || $messageId === '') {
                $failures[$index] = new TransportException('Resend did not return an ID for the email.');

                continue;
            }

            $messages[$index]->setMessageId($messageId);

            if ($messages[$index]->getOriginalMessage() instanceof Email) {
                $this->setResendEmailId($messages[$index]->getOriginalMessage(), $messageId);
            }
        }

        return $failures;
    }

    /**
     * Fail every given message because the Resend batch response could not be read.
     *
     * @param  array<int, SentMessage>  $messages
     * @return array<int, \Throwable>
     */
    protected function unreadableBatchResponse(array $messages): array
    {
        return array_map(fn () => new TransportException(
            'Unable to match the Resend API response to the email. It may or may not have been sent.'
        ), $messages);
    }
}
