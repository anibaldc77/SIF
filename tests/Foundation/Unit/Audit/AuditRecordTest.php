<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecord;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Exceptions\InvalidAuditPayloadKeyException;
use Sif\Foundation\Exceptions\InvalidAuditRecordException;
use Sif\Foundation\Exceptions\UnsupportedAuditPayloadValueException;

final class AuditRecordTest extends TestCase
{
    public function testAuditPayloadPreservesCompatibleValues(): void
    {
        $payload = new AuditPayload([
            'document' => [
                'id' => 'DOC-001',
                'signed' => true,
                'pages' => 5,
                'ratio' => 1.25,
                'metadata' => null,
                'roles' => ['author', 'reviewer'],
            ],
        ]);

        self::assertTrue($payload->has('document'));
        self::assertFalse($payload->isEmpty());
        self::assertSame('DOC-001', $payload->all()['document']['id']);
    }

    public function testAuditPayloadCanBeMergedWithoutMutation(): void
    {
        $base = new AuditPayload([
            'attempt' => 1,
            'request_id' => 'req-001',
        ]);

        $merged = $base->merged(new AuditPayload([
            'attempt' => 2,
            'worker' => 'queue-01',
        ]));

        self::assertSame(1, $base->get('attempt'));
        self::assertSame(2, $merged->get('attempt'));
        self::assertSame('req-001', $merged->get('request_id'));
        self::assertSame('queue-01', $merged->get('worker'));
    }

    public function testEmptyMergeReturnsSamePayloadInstance(): void
    {
        $payload = new AuditPayload(['id' => 1]);

        self::assertSame($payload, $payload->merged(new AuditPayload()));
    }

    public function testAuditPayloadRejectsEmptyKey(): void
    {
        $this->expectException(InvalidAuditPayloadKeyException::class);

        new AuditPayload([' ' => 'invalid']);
    }

    public function testAuditPayloadRejectsObjectsAndResources(): void
    {
        try {
            new AuditPayload(['object' => new \stdClass()]);
            self::fail('Objects must not be accepted.');
        } catch (UnsupportedAuditPayloadValueException) {
            self::assertTrue(true);
        }

        $resource = fopen('php://memory', 'rb');

        self::assertIsResource($resource);

        try {
            new AuditPayload(['resource' => $resource]);
            self::fail('Resources must not be accepted.');
        } catch (UnsupportedAuditPayloadValueException) {
            self::assertTrue(true);
        } finally {
            fclose($resource);
        }
    }

    public function testAuditPayloadRejectsNonFiniteFloat(): void
    {
        $this->expectException(UnsupportedAuditPayloadValueException::class);

        new AuditPayload(['ratio' => INF]);
    }

    public function testAuditRecordPreservesAllAuthoritativeValues(): void
    {
        $context = $this->context();
        $occurredAt = new DateTimeImmutable('2026-07-27T18:00:00.123456+00:00');
        $before = new AuditPayload(['status' => 'draft']);
        $after = new AuditPayload(['status' => 'signed']);
        $changes = new AuditPayload([
            'status' => [
                'before' => 'draft',
                'after' => 'signed',
            ],
        ]);

        $record = new AuditRecord(
            auditId: new AuditId('audit-001'),
            action: new AuditAction('document.signed'),
            level: AuditLevel::Notice,
            occurredAt: $occurredAt,
            context: $context,
            subject: new AuditSubject('document', 'DOC-001'),
            payload: new AuditPayload(['signature_method' => 'digital']),
            before: $before,
            after: $after,
            changes: $changes,
            tags: ['legal', 'document', 'legal'],
            schemaVersion: '1.0',
        );

        self::assertSame('audit-001', $record->auditId()->value());
        self::assertSame('document.signed', $record->action()->value());
        self::assertSame(AuditLevel::Notice, $record->level());
        self::assertSame($occurredAt, $record->occurredAt());
        self::assertSame($context, $record->context());
        self::assertSame('DOC-001', $record->subject()->identifier());
        self::assertSame('digital', $record->payload()->get('signature_method'));
        self::assertSame($before, $record->before());
        self::assertSame($after, $record->after());
        self::assertSame($changes, $record->changes());
        self::assertSame(['legal', 'document'], $record->tags());
        self::assertSame('1.0', $record->schemaVersion());
    }

    public function testAuditRecordSupportsMinimalOptionalState(): void
    {
        $record = new AuditRecord(
            auditId: new AuditId('audit-002'),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            occurredAt: new DateTimeImmutable('2026-07-27T18:05:00+00:00'),
            context: $this->context(),
            subject: new AuditSubject('system'),
        );

        self::assertTrue($record->payload()->isEmpty());
        self::assertNull($record->before());
        self::assertNull($record->after());
        self::assertNull($record->changes());
        self::assertSame([], $record->tags());
        self::assertSame('1.0', $record->schemaVersion());
    }

    public function testAuditRecordRejectsEmptyTag(): void
    {
        $this->expectException(InvalidAuditRecordException::class);

        new AuditRecord(
            auditId: new AuditId('audit-003'),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            occurredAt: new DateTimeImmutable(),
            context: $this->context(),
            subject: new AuditSubject('system'),
            tags: ['valid', ' '],
        );
    }

    public function testAuditRecordRejectsEmptySchemaVersion(): void
    {
        $this->expectException(InvalidAuditRecordException::class);

        new AuditRecord(
            auditId: new AuditId('audit-004'),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            occurredAt: new DateTimeImmutable(),
            context: $this->context(),
            subject: new AuditSubject('system'),
            schemaVersion: ' ',
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId('ctx-001'),
            correlationId: new ContextId('corr-001'),
            createdAt: new DateTimeImmutable('2026-07-27T17:59:00+00:00'),
            operation: 'document.sign',
            source: 'test',
            attributes: new ContextAttributes(['request_id' => 'req-001']),
        );
    }
}
