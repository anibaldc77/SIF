<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecordFactory;
use Sif\Foundation\Audit\AuditRecordSerializer;
use Sif\Foundation\Audit\AuditRedactionPolicy;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ContextRedactionPolicy;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Context\ExecutionContextSerializer;
use Sif\Foundation\Exceptions\InvalidAuditRedactionPolicyException;
use Sif\Foundation\Tests\Fixtures\Audit\SequenceAuditIdGenerator;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;

final class AuditFactorySerializationTest extends TestCase
{
    public function testFactoryUsesInjectedIdentifierGeneratorAndClock(): void
    {
        $occurredAt = new DateTimeImmutable('2026-07-27T20:00:00.654321+00:00');
        $factory = new AuditRecordFactory(
            new SequenceAuditIdGenerator(['audit-100']),
            new FrozenClock($occurredAt),
        );

        $record = $factory->create(
            context: $this->context(),
            action: new AuditAction('document.signed'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('document', 'DOC-100'),
            payload: new AuditPayload(['method' => 'digital']),
        );

        self::assertSame('audit-100', $record->auditId()->value());
        self::assertSame($occurredAt, $record->occurredAt());
        self::assertSame('document.signed', $record->action()->value());
        self::assertSame('DOC-100', $record->subject()->identifier());
    }

    public function testAuditRedactionPolicyRedactsMatchingKeysRecursively(): void
    {
        $policy = new AuditRedactionPolicy(
            keys: ['token', 'password'],
            marker: '***',
        );

        $result = $policy->redact([
            'token' => 'top-secret',
            'nested' => [
                'password' => 'hidden',
                'list' => [
                    ['token' => 'nested-secret'],
                ],
            ],
            'Token' => 'case-sensitive',
        ]);

        self::assertSame('***', $result['token']);
        self::assertSame('***', $result['nested']['password']);
        self::assertSame('***', $result['nested']['list'][0]['token']);
        self::assertSame('case-sensitive', $result['Token']);
    }

    public function testAuditRedactionPolicyRejectsEmptyKey(): void
    {
        $this->expectException(InvalidAuditRedactionPolicyException::class);

        new AuditRedactionPolicy([' ']);
    }

    public function testAuditRedactionPolicyRejectsEmptyMarker(): void
    {
        $this->expectException(InvalidAuditRedactionPolicyException::class);

        new AuditRedactionPolicy(['token'], ' ');
    }

    public function testSerializerProducesCanonicalDocumentAndAppliesBothPolicies(): void
    {
        $occurredAt = new DateTimeImmutable('2026-07-27T20:15:00.123456+00:00');
        $factory = new AuditRecordFactory(
            new SequenceAuditIdGenerator(['audit-200']),
            new FrozenClock($occurredAt),
        );

        $record = $factory->create(
            context: $this->context(),
            action: new AuditAction('document.updated'),
            level: AuditLevel::Warning,
            subject: new AuditSubject('document', 'DOC-200'),
            payload: new AuditPayload([
                'zeta' => 1,
                'token' => 'secret',
                'alpha' => [
                    'password' => 'hidden',
                    'visible' => true,
                ],
            ]),
            before: new AuditPayload(['status' => 'draft']),
            after: new AuditPayload(['status' => 'review']),
            changes: new AuditPayload([
                'status' => [
                    'before' => 'draft',
                    'after' => 'review',
                ],
            ]),
            tags: ['document', 'warning'],
            schemaVersion: '1.1',
        );

        $serializer = new AuditRecordSerializer(
            new ExecutionContextSerializer(),
            new ContextRedactionPolicy(['token'], '***'),
            new AuditRedactionPolicy(['token', 'password'], '***'),
        );

        $document = $serializer->serialize($record);

        self::assertSame(
            [
                'schema_version',
                'audit_id',
                'action',
                'level',
                'occurred_at',
                'context',
                'subject',
                'payload',
                'before',
                'after',
                'changes',
                'tags',
            ],
            array_keys($document),
        );

        self::assertSame('audit-200', $document['audit_id']);
        self::assertSame('document.updated', $document['action']);
        self::assertSame('warning', $document['level']);
        self::assertSame('2026-07-27T20:15:00.123456+00:00', $document['occurred_at']);
        self::assertSame('1.1', $document['schema_version']);
        self::assertSame('***', $document['context']['attributes']['token']);
        self::assertSame('***', $document['payload']['token']);
        self::assertSame('***', $document['payload']['alpha']['password']);
        self::assertSame(['alpha', 'token', 'zeta'], array_keys($document['payload']));
        self::assertSame(['document', 'warning'], $document['tags']);
    }

    public function testSerializerPreservesNullOptionalSnapshots(): void
    {
        $factory = new AuditRecordFactory(
            new SequenceAuditIdGenerator(['audit-300']),
            new FrozenClock(new DateTimeImmutable('2026-07-27T20:30:00+00:00')),
        );

        $record = $factory->create(
            context: $this->context(),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            subject: new AuditSubject('system'),
        );

        $document = $this->serializer()->serialize($record);

        self::assertNull($document['before']);
        self::assertNull($document['after']);
        self::assertNull($document['changes']);
        self::assertSame([], $document['payload']);
    }

    public function testSerializerIsDeterministicForEquivalentRecords(): void
    {
        $clock = new FrozenClock(
            new DateTimeImmutable('2026-07-27T20:45:00.000001+00:00'),
        );

        $factory = new AuditRecordFactory(
            new SequenceAuditIdGenerator(['audit-400', 'audit-400']),
            $clock,
        );

        $first = $factory->create(
            context: $this->context(),
            action: new AuditAction('case.updated'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('case', 'CASE-1'),
            payload: new AuditPayload([
                'b' => 2,
                'a' => 1,
            ]),
        );

        $second = $factory->create(
            context: $this->context(),
            action: new AuditAction('case.updated'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('case', 'CASE-1'),
            payload: new AuditPayload([
                'a' => 1,
                'b' => 2,
            ]),
        );

        self::assertSame(
            $this->serializer()->serialize($first),
            $this->serializer()->serialize($second),
        );
    }

    private function serializer(): AuditRecordSerializer
    {
        return new AuditRecordSerializer(
            new ExecutionContextSerializer(),
            new ContextRedactionPolicy([]),
            new AuditRedactionPolicy([]),
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId('ctx-audit'),
            correlationId: new ContextId('corr-audit'),
            createdAt: new DateTimeImmutable('2026-07-27T19:59:00+00:00'),
            actorId: 'actor-1',
            operation: 'document.update',
            source: 'unit-test',
            attributes: new ContextAttributes([
                'token' => 'context-secret',
                'request_id' => 'req-200',
            ]),
        );
    }
}
