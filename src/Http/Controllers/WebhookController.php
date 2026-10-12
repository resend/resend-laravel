<?php

namespace Resend\Laravel\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Str;
use Resend\Laravel\Events\ContactCreated;
use Resend\Laravel\Events\ContactDeleted;
use Resend\Laravel\Events\ContactTopicsUpdated;
use Resend\Laravel\Events\ContactUpdated;
use Resend\Laravel\Events\DomainCreated;
use Resend\Laravel\Events\DomainDeleted;
use Resend\Laravel\Events\DomainUpdated;
use Resend\Laravel\Events\EmailBounced;
use Resend\Laravel\Events\EmailClicked;
use Resend\Laravel\Events\EmailComplained;
use Resend\Laravel\Events\EmailDelivered;
use Resend\Laravel\Events\EmailDeliveryDelayed;
use Resend\Laravel\Events\EmailFailed;
use Resend\Laravel\Events\EmailOpened;
use Resend\Laravel\Events\EmailReceived;
use Resend\Laravel\Events\EmailScheduled;
use Resend\Laravel\Events\EmailSent;
use Resend\Laravel\Events\EmailSuppressed;
use Resend\Laravel\Events\InboxCreated;
use Resend\Laravel\Events\InboxDeleted;
use Resend\Laravel\Events\InboxDraftCreated;
use Resend\Laravel\Events\InboxDraftDeleted;
use Resend\Laravel\Events\InboxDraftSent;
use Resend\Laravel\Events\InboxDraftUpdated;
use Resend\Laravel\Events\InboxEmailReceived;
use Resend\Laravel\Events\InboxEmailSent;
use Resend\Laravel\Events\InboxThreadAssigned;
use Resend\Laravel\Events\InboxThreadCreated;
use Resend\Laravel\Events\InboxThreadFolderUpdated;
use Resend\Laravel\Events\InboxThreadLabelsUpdated;
use Resend\Laravel\Events\InboxThreadUnassigned;
use Resend\Laravel\Events\InboxUpdated;
use Resend\Laravel\Events\SuppressionAdded;
use Resend\Laravel\Events\SuppressionRemoved;
use Resend\Laravel\Events\TopicCreated;
use Resend\Laravel\Events\TopicDeleted;
use Resend\Laravel\Events\TopicUpdated;
use Resend\Laravel\Http\Middleware\VerifyWebhookSignature;
use Symfony\Component\HttpFoundation\Response;

class WebhookController extends Controller
{
    /**
     * Create a new webhook controller instance.
     */
    public function __construct()
    {
        if (config('resend.webhook.secret')) {
            $this->middleware(VerifyWebhookSignature::class);
        }
    }

    /**
     * Handle a Resend webhook call.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = json_decode($request->getContent(), true);

        if (is_null($payload) || ! isset($payload['type'])) {
            return new Response('Invalid payload', 400);
        }

        $headers = $this->extractHeaders($request);

        $method = 'handle' . Str::studly(str_replace('.', '_', $payload['type']));

        if (method_exists($this, $method)) {
            $response = $this->{$method}($payload, $headers);

            return $response;
        }

        return $this->missingMethod($payload);
    }

    /**
     * Extract the Svix webhook headers from the request so listeners can use
     * them (e.g. `svix-id` as a deduplication key).
     *
     * @return array<string, string>
     */
    protected function extractHeaders(Request $request): array
    {
        return collect(['svix-id', 'svix-timestamp', 'svix-signature'])
            ->mapWithKeys(fn (string $key) => [$key => $request->header($key)])
            ->filter()
            ->all();
    }

    /**
     * Handle contact created event.
     */
    protected function handleContactCreated(array $payload, array $headers = []): Response
    {
        ContactCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle contact deleted event.
     */
    protected function handleContactDeleted(array $payload, array $headers = []): Response
    {
        ContactDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle contact topics updated event.
     */
    protected function handleContactTopicsUpdated(array $payload, array $headers = []): Response
    {
        ContactTopicsUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle contact updated event.
     */
    protected function handleContactUpdated(array $payload, array $headers = []): Response
    {
        ContactUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain created event.
     */
    protected function handleDomainCreated(array $payload, array $headers = []): Response
    {
        DomainCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain deleted event.
     */
    protected function handleDomainDeleted(array $payload, array $headers = []): Response
    {
        DomainDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle domain updated event.
     */
    protected function handleDomainUpdated(array $payload, array $headers = []): Response
    {
        DomainUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email bounced event.
     */
    protected function handleEmailBounced(array $payload, array $headers = []): Response
    {
        EmailBounced::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email clicked event.
     */
    protected function handleEmailClicked(array $payload, array $headers = []): Response
    {
        EmailClicked::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email complained event.
     */
    protected function handleEmailComplained(array $payload, array $headers = []): Response
    {
        EmailComplained::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email delivered event.
     */
    protected function handleEmailDelivered(array $payload, array $headers = []): Response
    {
        EmailDelivered::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email delivery delayed event.
     */
    protected function handleEmailDeliveryDelayed(array $payload, array $headers = []): Response
    {
        EmailDeliveryDelayed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email opened event.
     */
    protected function handleEmailOpened(array $payload, array $headers = []): Response
    {
        EmailOpened::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email sent event.
     */
    protected function handleEmailSent(array $payload, array $headers = []): Response
    {
        EmailSent::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email failed event.
     */
    protected function handleEmailFailed(array $payload, array $headers = []): Response
    {
        EmailFailed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email suppressed event.
     */
    protected function handleEmailSuppressed(array $payload, array $headers = []): Response
    {
        EmailSuppressed::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email scheduled event.
     */
    protected function handleEmailScheduled(array $payload, array $headers = []): Response
    {
        EmailScheduled::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle email received event.
     */
    protected function handleEmailReceived(array $payload, array $headers = []): Response
    {
        EmailReceived::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox created event.
     */
    protected function handleInboxCreated(array $payload, array $headers = []): Response
    {
        InboxCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox deleted event.
     */
    protected function handleInboxDeleted(array $payload, array $headers = []): Response
    {
        InboxDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox draft created event.
     */
    protected function handleInboxDraftCreated(array $payload, array $headers = []): Response
    {
        InboxDraftCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox draft deleted event.
     */
    protected function handleInboxDraftDeleted(array $payload, array $headers = []): Response
    {
        InboxDraftDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox draft sent event.
     */
    protected function handleInboxDraftSent(array $payload, array $headers = []): Response
    {
        InboxDraftSent::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox draft updated event.
     */
    protected function handleInboxDraftUpdated(array $payload, array $headers = []): Response
    {
        InboxDraftUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox email received event.
     */
    protected function handleInboxEmailReceived(array $payload, array $headers = []): Response
    {
        InboxEmailReceived::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox email sent event.
     */
    protected function handleInboxEmailSent(array $payload, array $headers = []): Response
    {
        InboxEmailSent::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox thread assigned event.
     */
    protected function handleInboxThreadAssigned(array $payload, array $headers = []): Response
    {
        InboxThreadAssigned::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox thread created event.
     */
    protected function handleInboxThreadCreated(array $payload, array $headers = []): Response
    {
        InboxThreadCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox thread folder updated event.
     */
    protected function handleInboxThreadFolderUpdated(array $payload, array $headers = []): Response
    {
        InboxThreadFolderUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox thread labels updated event.
     */
    protected function handleInboxThreadLabelsUpdated(array $payload, array $headers = []): Response
    {
        InboxThreadLabelsUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox thread unassigned event.
     */
    protected function handleInboxThreadUnassigned(array $payload, array $headers = []): Response
    {
        InboxThreadUnassigned::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle inbox updated event.
     */
    protected function handleInboxUpdated(array $payload, array $headers = []): Response
    {
        InboxUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle suppression added event.
     */
    protected function handleSuppressionAdded(array $payload, array $headers = []): Response
    {
        SuppressionAdded::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle suppression removed event.
     */
    protected function handleSuppressionRemoved(array $payload, array $headers = []): Response
    {
        SuppressionRemoved::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle topic created event.
     */
    protected function handleTopicCreated(array $payload, array $headers = []): Response
    {
        TopicCreated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle topic deleted event.
     */
    protected function handleTopicDeleted(array $payload, array $headers = []): Response
    {
        TopicDeleted::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle topic updated event.
     */
    protected function handleTopicUpdated(array $payload, array $headers = []): Response
    {
        TopicUpdated::dispatch($payload, $headers);

        return $this->successMethod();
    }

    /**
     * Handle successful calls on the controller.
     */
    protected function successMethod($parameters = []): Response
    {
        return new Response('Webhook handled', 200);
    }

    /**
     * Handle calls to missing methods on the controller.
     */
    protected function missingMethod($parameters = []): Response
    {
        return new Response;
    }
}
