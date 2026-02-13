<?php

namespace Resend\Laravel\Transport;

use Exception;
use Resend\Contracts\Client;
use Symfony\Component\Mailer\Envelope;
use Symfony\Component\Mailer\Exception\TransportException;
use Symfony\Component\Mailer\Header\MetadataHeader;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\MessageConverter;

class ResendTransportFactory extends AbstractTransport
{
    /**
     * Create a new Resend transport instance.
     */
    public function __construct(
        protected Client $resend,
        protected array $config = []
    ) {
        parent::__construct();
    }

    /**
     * {@inheritDoc}
     */
    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        $envelope = $message->getEnvelope();

        $headers = [];
        $tags = [];
        $headersToBypass = ['from', 'to', 'cc', 'bcc', 'subject', 'content-type', 'sender', 'reply-to', 'resend-idempotency-key'];
        foreach ($email->getHeaders()->all() as $name => $header) {
            if ($header instanceof MetadataHeader) {
                $tags[] = ['name' => $header->getKey(), 'value' => $header->getValue()];

                continue;
            }

            if (in_array($name, $headersToBypass, true)) {
                continue;
            }

            $headers[$header->getName()] = $header->getBodyAsString();
        }

        $options = [];

        if ($email->getHeaders()->has('Resend-Idempotency-Key')) {
            $options['idempotency_key'] = $email->getHeaders()->get('Resend-Idempotency-Key')->getBodyAsString();
        }

        try {
            $result = $this->resend->emails->send([
                'bcc' => $this->stringifyAddresses($email->getBcc()),
                'cc' => $this->stringifyAddresses($email->getCc()),
                'from' => $envelope->getSender()->toString(),
                'headers' => $headers,
                'html' => $email->getHtmlBody(),
                'reply_to' => $this->stringifyAddresses($email->getReplyTo()),
                'subject' => $email->getSubject(),
                'tags' => $tags,
                'text' => $email->getTextBody(),
                'to' => $this->stringifyAddresses($this->getRecipients($email, $envelope)),
                'attachments' => $this->getAttachments($email),
            ], $options);
        } catch (Exception $exception) {
            throw new TransportException(
                sprintf('Request to the Resend API failed. Reason: %s', $exception->getMessage()),
                is_int($exception->getCode()) ? $exception->getCode() : 0,
                $exception
            );
        }

        $messageId = $result->id ?? null;
        $statusCode = $result->statusCode ?? 0;
        
        if ($statusCode >= 400 || !is_string($messageId) || $messageId === '') {
            throw new TransportException(
                sprintf('Request to the Resend API failed. Reason: %s', $result->message ?? 'Unknown error'),
                $statusCode
            );
        }

        $email->getHeaders()->addHeader('X-Resend-Email-ID', $messageId);
    }

    /**
     * Get the recipients without CC or BCC.
     */
    protected function getRecipients(Email $email, Envelope $envelope): array
    {
        return array_filter($envelope->getRecipients(), function (Address $address) use ($email) {
            return in_array($address, array_merge($email->getCc(), $email->getBcc()), true) === false;
        });
    }

    /**
     * Get the attachments.
     */
    protected function getAttachments(Email $email): array
    {
        $attachments = [];
        if ($email->getAttachments()) {
            foreach ($email->getAttachments() as $attachment) {
                $attachmentHeaders = $attachment->getPreparedHeaders();

                $contentType = $attachmentHeaders->get('Content-Type')->getBody();
                $disposition = $attachmentHeaders->getHeaderBody('Content-Disposition');
                $filename = $attachmentHeaders->getHeaderParameter('Content-Disposition', 'filename');

                if ($contentType == 'text/calendar') {
                    $content = $attachment->getBody();
                } else {
                    $content = str_replace("\r\n", '', $attachment->bodyToString());
                }

                $item = [
                    'content_type' => $contentType,
                    'content' => $content,
                    'filename' => $filename,
                ];

                if ($disposition === 'inline') {
                    $item['content_id'] = $attachment->hasContentId() ? $attachment->getContentId() : $filename;
                }

                $attachments[] = $item;
            }
        }

        return $attachments;
    }

    /**
     * Get the string representation of the transport.
     */
    public function __toString(): string
    {
        return 'resend';
    }
}
