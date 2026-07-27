<?php

declare(strict_types=1);

use DateTimeImmutable;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecordCreated;
use Sif\Foundation\Audit\AuditRecordFactory;
use Sif\Foundation\Audit\AuditRecordSerializer;
use Sif\Foundation\Audit\AuditRedactionPolicy;
use Sif\Foundation\Audit\AuditService;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Audit\EventDispatcherAuditEmitter;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ContextRedactionPolicy;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Context\ExecutionContextSerializer;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Tests\Fixtures\Audit\SequenceAuditIdGenerator;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;

require dirname(__DIR__) . '/vendor/autoload.php';

$context = new ExecutionContext(
    contextId: new ContextId('ctx-reference'),
    correlationId: new ContextId('corr-reference'),
    createdAt: new DateTimeImmutable('2026-07-27T23:00:00+00:00'),
    actorId: 'actor-001',
    operation: 'document.sign',
    source: 'example',
    attributes: new ContextAttributes([
        'request_id' => 'req-001',
        'token' => 'context-secret',
    ]),
);

$provider = new ListenerProvider();
$dispatcher = new EventDispatcher($provider);

$serializer = new AuditRecordSerializer(
    new ExecutionContextSerializer(),
    new ContextRedactionPolicy(['token']),
    new AuditRedactionPolicy(['token', 'password']),
);

$documents = [];

$provider->add(
    AuditRecordCreated::class,
    static function (object $event) use (&$documents, $serializer): void {
        if (!$event instanceof AuditRecordCreated) {
            return;
        }

        $documents[] = $serializer->serialize($event->record());
    },
);

$service = new AuditService(
    new AuditRecordFactory(
        new SequenceAuditIdGenerator(['audit-reference-001']),
        new FrozenClock(
            new DateTimeImmutable('2026-07-27T23:00:01.123456+00:00'),
        ),
    ),
    new EventDispatcherAuditEmitter($dispatcher),
);

$record = $service->record(
    context: $context,
    action: new AuditAction('document.signed'),
    level: AuditLevel::Notice,
    subject: new AuditSubject('document', 'DOC-001'),
    payload: new AuditPayload([
        'signature_method' => 'digital',
        'token' => 'audit-secret',
    ]),
    before: new AuditPayload(['status' => 'draft']),
    after: new AuditPayload(['status' => 'signed']),
    changes: new AuditPayload([
        'status' => [
            'before' => 'draft',
            'after' => 'signed',
        ],
    ]),
    tags: ['legal', 'document'],
);

$document = $documents[0];

echo 'Audit ID: ' . $record->auditId()->value() . PHP_EOL;
echo 'Action: ' . $record->action()->value() . PHP_EOL;
echo 'Level: ' . $record->level()->value . PHP_EOL;
echo 'Subject: ' . $record->subject()->type() . ':' . $record->subject()->identifier() . PHP_EOL;
echo 'Context token: ' . $document['context']['attributes']['token'] . PHP_EOL;
echo 'Payload token: ' . $document['payload']['token'] . PHP_EOL;
echo 'Captured documents: ' . count($documents) . PHP_EOL;
