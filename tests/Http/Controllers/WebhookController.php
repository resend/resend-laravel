<?php

use Illuminate\Support\Facades\Event;
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
use Resend\Laravel\Http\Controllers\WebhookController as Controller;

test('correct methods are called and handled based on resend webhook event', function (string $name, string $event) {
    $request = webhookRequest($name);

    Event::fake([
        $event,
    ]);

    $response = (new Controller)->handleWebhook($request);

    Event::assertDispatched($event, function ($e) use ($request) {
        return $request->getContent() == json_encode($e->payload)
            && $e->headers === [
                'svix-id' => 'msg_123456789',
                'svix-timestamp' => '1614265330',
                'svix-signature' => 'v1,exampleSignature',
            ];
    });

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBe('Webhook handled');
})->with([
    ['contact.created', ContactCreated::class],
    ['contact.deleted', ContactDeleted::class],
    ['contact.topics.updated', ContactTopicsUpdated::class],
    ['contact.updated', ContactUpdated::class],
    ['domain.created', DomainCreated::class],
    ['domain.deleted', DomainDeleted::class],
    ['domain.updated', DomainUpdated::class],
    ['email.bounced', EmailBounced::class],
    ['email.clicked', EmailClicked::class],
    ['email.complained', EmailComplained::class],
    ['email.delivered', EmailDelivered::class],
    ['email.delivery_delayed', EmailDeliveryDelayed::class],
    ['email.opened', EmailOpened::class],
    ['email.sent', EmailSent::class],
    ['email.failed', EmailFailed::class],
    ['email.suppressed', EmailSuppressed::class],
    ['email.scheduled', EmailScheduled::class],
    ['email.received', EmailReceived::class],
    ['inbox.created', InboxCreated::class],
    ['inbox.deleted', InboxDeleted::class],
    ['inbox.draft.created', InboxDraftCreated::class],
    ['inbox.draft.deleted', InboxDraftDeleted::class],
    ['inbox.draft.sent', InboxDraftSent::class],
    ['inbox.draft.updated', InboxDraftUpdated::class],
    ['inbox.email.received', InboxEmailReceived::class],
    ['inbox.email.sent', InboxEmailSent::class],
    ['inbox.thread.assigned', InboxThreadAssigned::class],
    ['inbox.thread.created', InboxThreadCreated::class],
    ['inbox.thread.folder.updated', InboxThreadFolderUpdated::class],
    ['inbox.thread.labels.updated', InboxThreadLabelsUpdated::class],
    ['inbox.thread.unassigned', InboxThreadUnassigned::class],
    ['inbox.updated', InboxUpdated::class],
    ['suppression.added', SuppressionAdded::class],
    ['suppression.removed', SuppressionRemoved::class],
    ['topic.created', TopicCreated::class],
    ['topic.deleted', TopicDeleted::class],
    ['topic.updated', TopicUpdated::class],
]);

test('svix-id is forwarded to the event so it can be used as a dedup key', function () {
    $request = webhookRequest('email.sent');

    Event::fake([EmailSent::class]);

    (new Controller)->handleWebhook($request);

    Event::assertDispatched(EmailSent::class, function ($e) {
        return $e->headers['svix-id'] === 'msg_123456789';
    });
});

test('event receives empty headers when svix headers are absent', function () {
    $request = Illuminate\Http\Request::create('/', 'POST', [], [], [], [], json_encode([
        'id' => 're_evt_123456789',
        'type' => 'email.sent',
    ]));

    Event::fake([EmailSent::class]);

    (new Controller)->handleWebhook($request);

    Event::assertDispatched(EmailSent::class, function ($e) {
        return $e->headers === [];
    });
});

test('normal response is returned if method is missing', function () {
    $request = webhookRequest('email.foo');

    Event::fake();

    $response = (new Controller)->handleWebhook($request);

    Event::assertNothingDispatched();

    expect($response->getStatusCode())->toBe(200)
        ->and($response->getContent())->toBeEmpty();
});

test('verify webhook signature middleware is called when webhook secret is set', function () {
    config(['resend.webhook.secret' => 'secret']);
    config(['resend.webhook.tolerance' => 300]);

    Event::fake([
        EmailDelivered::class,
    ]);

    $this->postJson('/resend/webhook', [
        'id' => 're_evt_123456789',
        'type' => 'email.delivered',
    ])->assertForbidden();

    Event::assertNothingDispatched();
});

// This function creates a new Request object with a JSON body that does not include the fields expected by your controller.
function invalidWebhookRequest()
{
    return new Illuminate\Http\Request([], [], [], [], [], ['CONTENT_TYPE' => 'application/json'], json_encode(['invalid' => 'data']));
}

test('it returns an error response for invalid payload', function () {
    $request = invalidWebhookRequest();

    $response = (new Controller)->handleWebhook($request);

    expect($response->getStatusCode())->toBe(400);
});
