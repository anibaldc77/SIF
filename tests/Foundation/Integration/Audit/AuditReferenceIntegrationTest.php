<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Integration\Audit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditModelDescriber;
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
use Sif\Foundation\Contracts\AuditChangeSetProviderInterface;
use Sif\Foundation\Contracts\AuditMetadataProviderInterface;
use Sif\Foundation\Contracts\AuditableSubjectInterface;
use Sif\Foundation\Contracts\AuditSnapshotProviderInterface;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;
use Sif\Foundation\Tests\Fixtures\Audit\SequenceAuditIdGenerator;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;

final class AuditReferenceIntegrationTest extends TestCase
{
    public function testEndToEndAuditFlowProducesOneCanonicalRedactedDocument(): void
    {
        $documents = [];
        $serializer = $this->serializer();
        $provider = new ListenerProvider();

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$documents, $serializer): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);

                $documents[] = $serializer->serialize($event->record());
            },
        );

        $service = new AuditService(
            new AuditRecordFactory(
                new SequenceAuditIdGenerator(['audit-integration-001']),
                new FrozenClock(
                    new DateTimeImmutable('2026-07-27T23:10:00.123456+00:00'),
                ),
            ),
            new EventDispatcherAuditEmitter(
                new EventDispatcher($provider),
            ),
        );

        $context = $this->context();

        $record = $service->record(
            context: $context,
            action: new AuditAction('document.signed'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('document', 'DOC-001'),
            payload: new AuditPayload([
                'token' => 'audit-secret',
                'signature_method' => 'digital',
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

        self::assertSame('audit-integration-001', $record->auditId()->value());
        self::assertSame($context, $record->context());
        self::assertCount(1, $documents);
        self::assertSame('[REDACTED]', $documents[0]['context']['attributes']['token']);
        self::assertSame('[REDACTED]', $documents[0]['payload']['token']);
        self::assertSame('digital', $documents[0]['payload']['signature_method']);
        self::assertSame('signed', $documents[0]['after']['status']);
        self::assertSame(['legal', 'document'], $documents[0]['tags']);
    }

    public function testModelDescriptionCanFeedAuditRecordConstructionExplicitly(): void
    {
        $model = new class implements
            AuditableSubjectInterface,
            AuditMetadataProviderInterface,
            AuditSnapshotProviderInterface,
            AuditChangeSetProviderInterface
        {
            public function auditSubject(): AuditSubject
            {
                return new AuditSubject('case', 'CASE-001');
            }

            public function auditMetadata(): AuditPayload
            {
                return new AuditPayload([
                    'module' => 'legal',
                ]);
            }

            public function auditSnapshot(): AuditPayload
            {
                return new AuditPayload([
                    'status' => 'resolved',
                ]);
            }

            public function auditChanges(): AuditPayload
            {
                return new AuditPayload([
                    'status' => [
                        'before' => 'pending',
                        'after' => 'resolved',
                    ],
                ]);
            }
        };

        $description = (new AuditModelDescriber())->describe($model);
        $captured = null;
        $provider = new ListenerProvider();

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$captured): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                $captured = $event->record();
            },
        );

        $service = new AuditService(
            new AuditRecordFactory(
                new SequenceAuditIdGenerator(['audit-integration-002']),
                new FrozenClock(
                    new DateTimeImmutable('2026-07-27T23:20:00+00:00'),
                ),
            ),
            new EventDispatcherAuditEmitter(
                new EventDispatcher($provider),
            ),
        );

        $record = $service->record(
            context: $this->context(),
            action: new AuditAction('case.resolved'),
            level: AuditLevel::Notice,
            subject: $description->subject(),
            payload: $description->metadata(),
            after: $description->snapshot(),
            changes: $description->changes(),
        );

        self::assertSame($record, $captured);
        self::assertSame('CASE-001', $record->subject()->identifier());
        self::assertSame('legal', $record->payload()->get('module'));
        self::assertSame('resolved', $record->after()?->get('status'));
        self::assertSame(
            'pending',
            $record->changes()?->all()['status']['before'],
        );
    }

    public function testAuditReferenceFlowRemainsStorageNeutral(): void
    {
        $provider = new ListenerProvider();
        $records = [];

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$records): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                $records[] = $event->record();
            },
        );

        $service = new AuditService(
            new AuditRecordFactory(
                new SequenceAuditIdGenerator(['audit-integration-003']),
                new FrozenClock(
                    new DateTimeImmutable('2026-07-27T23:30:00+00:00'),
                ),
            ),
            new EventDispatcherAuditEmitter(
                new EventDispatcher($provider),
            ),
        );

        $record = $service->record(
            context: $this->context(),
            action: new AuditAction('system.checked'),
            level: AuditLevel::Diagnostic,
            subject: new AuditSubject('system'),
        );

        self::assertCount(1, $records);
        self::assertSame($record, $records[0]);
        self::assertSame([], $record->payload()->all());
        self::assertNull($record->before());
        self::assertNull($record->after());
        self::assertNull($record->changes());
    }

    private function serializer(): AuditRecordSerializer
    {
        return new AuditRecordSerializer(
            new ExecutionContextSerializer(),
            new ContextRedactionPolicy(['token']),
            new AuditRedactionPolicy(['token', 'password']),
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId('ctx-audit-integration'),
            correlationId: new ContextId('corr-audit-integration'),
            createdAt: new DateTimeImmutable('2026-07-27T23:00:00+00:00'),
            actorId: 'actor-001',
            tenantId: 'tenant-001',
            operation: 'audit.integration',
            source: 'integration-test',
            attributes: new ContextAttributes([
                'request_id' => 'req-001',
                'token' => 'context-secret',
            ]),
        );
    }
}
