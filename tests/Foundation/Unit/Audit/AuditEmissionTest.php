<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditRecord;
use Sif\Foundation\Audit\AuditRecordCreated;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Audit\EventDispatcherAuditEmitter;
use Sif\Foundation\Audit\NullAuditEmitter;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;
use Sif\Foundation\Event\EventDispatcher;
use Sif\Foundation\Event\ListenerProvider;

final class AuditEmissionTest extends TestCase
{
    public function testAuditRecordCreatedPreservesRecordIdentity(): void
    {
        $record = $this->record();

        $event = new AuditRecordCreated($record);

        self::assertSame($record, $event->record());
    }

    public function testEmitterDispatchesAuditRecordCreatedAndReturnsSameRecord(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $received = [];

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$received): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                $received[] = $event;
            },
        );

        $record = $this->record();
        $emitter = new EventDispatcherAuditEmitter($dispatcher);

        $result = $emitter->emit($record);

        self::assertSame($record, $result);
        self::assertCount(1, $received);
        self::assertSame($record, $received[0]->record());
    }

    public function testEmitterUsesDispatcherOrderingRules(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $order = [];

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$order): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                $order[] = 'low';
            },
            priority: 100,
        );

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use (&$order): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                $order[] = 'high';
            },
            priority: 200,
        );

        (new EventDispatcherAuditEmitter($dispatcher))->emit($this->record());

        self::assertSame(['high', 'low'], $order);
    }

    public function testListenerFailurePropagatesAccordingToDispatcherPolicy(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $failure = new RuntimeException('audit listener failure');

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use ($failure): never {
                self::assertInstanceOf(AuditRecordCreated::class, $event);

                throw $failure;
            },
        );

        $this->expectExceptionObject($failure);

        (new EventDispatcherAuditEmitter($dispatcher))->emit($this->record());
    }

    public function testNullEmitterReturnsSameRecordWithoutDispatching(): void
    {
        $record = $this->record();

        $result = (new NullAuditEmitter())->emit($record);

        self::assertSame($record, $result);
    }

    public function testEmitterDoesNotSerializeOrMutateRecord(): void
    {
        $provider = new ListenerProvider();
        $dispatcher = new EventDispatcher($provider);
        $record = $this->record();

        $provider->add(
            AuditRecordCreated::class,
            static function (object $event) use ($record): void {
                self::assertInstanceOf(AuditRecordCreated::class, $event);
                self::assertSame($record, $event->record());
            },
        );

        $result = (new EventDispatcherAuditEmitter($dispatcher))->emit($record);

        self::assertSame($record, $result);
        self::assertSame('audit-500', $record->auditId()->value());
        self::assertSame('system.started', $record->action()->value());
    }

    private function record(): AuditRecord
    {
        $context = new ExecutionContext(
            contextId: new ContextId('ctx-audit-emission'),
            correlationId: new ContextId('corr-audit-emission'),
            createdAt: new DateTimeImmutable('2026-07-27T21:00:00+00:00'),
            operation: 'system.start',
            source: 'unit-test',
        );

        return new AuditRecord(
            auditId: new AuditId('audit-500'),
            action: new AuditAction('system.started'),
            level: AuditLevel::Informational,
            occurredAt: new DateTimeImmutable('2026-07-27T21:00:01+00:00'),
            context: $context,
            subject: new AuditSubject('system'),
        );
    }
}
