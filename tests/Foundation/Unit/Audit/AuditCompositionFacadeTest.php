<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\Audit;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditPayload;
use Sif\Foundation\Audit\AuditRecordFactory;
use Sif\Foundation\Audit\AuditService;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Exceptions\AuditNotConfiguredException;
use Sif\Foundation\Tests\Fixtures\Audit\RecordingAuditEmitter;
use Sif\Foundation\Tests\Fixtures\Audit\SequenceAuditIdGenerator;
use Sif\Foundation\Tests\Fixtures\Context\FrozenClock;

final class AuditCompositionFacadeTest extends TestCase
{
    protected function tearDown(): void
    {
        Audit::reset();
    }

    public function testInstanceServiceCreatesEmitsAndReturnsSameRecord(): void
    {
        $emitter = new RecordingAuditEmitter();
        $service = $this->service($emitter, ['audit-700']);

        $record = $service->record(
            context: $this->context(),
            action: new AuditAction('document.created'),
            level: AuditLevel::Informational,
            subject: new AuditSubject('document', 'DOC-700'),
            payload: new AuditPayload(['source' => 'unit-test']),
        );

        self::assertSame('audit-700', $record->auditId()->value());
        self::assertCount(1, $emitter->records());
        self::assertSame($record, $emitter->records()[0]);
    }

    public function testFacadeFailsPredictablyWhenNotConfigured(): void
    {
        self::assertFalse(Audit::isConfigured());

        $this->expectException(AuditNotConfiguredException::class);

        Audit::record(
            context: $this->context(),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            subject: new AuditSubject('system'),
        );
    }

    public function testFacadeDelegatesToConfiguredInstanceService(): void
    {
        $emitter = new RecordingAuditEmitter();
        $service = $this->service($emitter, ['audit-701']);

        Audit::configure($service);

        $record = Audit::record(
            context: $this->context(),
            action: new AuditAction('document.updated'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('document', 'DOC-701'),
            tags: ['document', 'update'],
        );

        self::assertTrue(Audit::isConfigured());
        self::assertSame('audit-701', $record->auditId()->value());
        self::assertSame(['document', 'update'], $record->tags());
        self::assertSame($record, $emitter->records()[0]);
    }

    public function testFacadeRequiresContextOnEveryCall(): void
    {
        $emitter = new RecordingAuditEmitter();
        Audit::configure($this->service($emitter, ['audit-702']));

        $context = $this->context();

        $record = Audit::record(
            context: $context,
            action: new AuditAction('case.reviewed'),
            level: AuditLevel::Notice,
            subject: new AuditSubject('case', 'CASE-702'),
        );

        self::assertSame($context, $record->context());
    }

    public function testResetRemovesOnlyFacadeConfiguration(): void
    {
        $emitter = new RecordingAuditEmitter();
        $service = $this->service($emitter, ['audit-703']);

        Audit::configure($service);
        Audit::reset();

        self::assertFalse(Audit::isConfigured());

        $record = $service->record(
            context: $this->context(),
            action: new AuditAction('system.checked'),
            level: AuditLevel::Diagnostic,
            subject: new AuditSubject('system'),
        );

        self::assertSame('audit-703', $record->auditId()->value());
        self::assertCount(1, $emitter->records());
    }

    public function testFacadeMayBeReconfiguredExplicitly(): void
    {
        $firstEmitter = new RecordingAuditEmitter();
        $secondEmitter = new RecordingAuditEmitter();

        Audit::configure($this->service($firstEmitter, ['audit-first']));
        Audit::configure($this->service($secondEmitter, ['audit-second']));

        $record = Audit::record(
            context: $this->context(),
            action: new AuditAction('system.reconfigured'),
            level: AuditLevel::Warning,
            subject: new AuditSubject('system'),
        );

        self::assertSame('audit-second', $record->auditId()->value());
        self::assertCount(0, $firstEmitter->records());
        self::assertCount(1, $secondEmitter->records());
    }

    /**
     * @param list<string> $ids
     */
    private function service(
        RecordingAuditEmitter $emitter,
        array $ids,
    ): AuditService {
        return new AuditService(
            new AuditRecordFactory(
                new SequenceAuditIdGenerator($ids),
                new FrozenClock(
                    new DateTimeImmutable('2026-07-27T22:00:00+00:00'),
                ),
            ),
            $emitter,
        );
    }

    private function context(): ExecutionContext
    {
        return new ExecutionContext(
            contextId: new ContextId('ctx-audit-service'),
            correlationId: new ContextId('corr-audit-service'),
            createdAt: new DateTimeImmutable('2026-07-27T21:59:00+00:00'),
            operation: 'audit.test',
            source: 'unit-test',
        );
    }
}
