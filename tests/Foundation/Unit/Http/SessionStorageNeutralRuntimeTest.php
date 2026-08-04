<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\SessionRecord;
use Sif\Foundation\Session\SessionRuntime;
use Sif\Foundation\Session\Storage\InMemorySessionStore;

final class SessionStorageNeutralRuntimeTest extends TestCase
{
    public function testRuntimeCreatesPersistsAndReopensState(): void
    {
        $store = new InMemorySessionStore();
        $runtime = new SessionRuntime($store, new SequentialSessionIdGenerator(), new FixedSessionClock('2030-01-01T00:00:00+00:00'));
        $opened = $runtime->open(null);
        $opened->state()->put('user', 25);
        $runtime->commit($opened->state());

        $reopened = $runtime->open($opened->state()->id()->value());
        self::assertTrue($reopened->identifierAccepted());
        self::assertSame(25, $reopened->state()->get('user'));
    }

    public function testExpiredRecordIsDeletedAndReplaced(): void
    {
        $store = new InMemorySessionStore();
        $old = new SessionId(str_repeat('a', 32));
        $store->write(new SessionRecord($old, ['stale' => true], new DateTimeImmutable('2030-01-01T00:00:00+00:00'), new DateTimeImmutable('2030-01-01T00:00:00+00:00')));
        $runtime = new SessionRuntime($store, new SequentialSessionIdGenerator(), new FixedSessionClock('2030-01-01T01:00:00+00:00'), new SessionPolicy(7200, 1800));

        $opened = $runtime->open($old->value());
        self::assertTrue($opened->expiredRecordDiscarded());
        self::assertFalse($opened->identifierAccepted());
        self::assertFalse($opened->state()->has('stale'));
    }

    public function testRegenerationInvalidatesPreviousIdentifier(): void
    {
        $store = new InMemorySessionStore();
        $runtime = new SessionRuntime($store, new SequentialSessionIdGenerator(), new FixedSessionClock('2030-01-01T00:00:00+00:00'));
        $state = $runtime->open(null)->state();
        $runtime->commit($state);
        $previous = $state->id()->value();
        $state->requestRegeneration();
        $runtime->commit($state);

        self::assertNotSame($previous, $state->id()->value());
        self::assertFalse($runtime->open($previous)->identifierAccepted());
    }

    public function testDestroyDeletesStoredRecord(): void
    {
        $store = new InMemorySessionStore();
        $runtime = new SessionRuntime($store, new SequentialSessionIdGenerator(), new FixedSessionClock('2030-01-01T00:00:00+00:00'));
        $state = $runtime->open(null)->state();
        $runtime->commit($state);
        $state->destroy();
        $runtime->commit($state);
        self::assertSame(0, $store->count());
    }
}

final class FixedSessionClock implements ClockInterface
{
    public function __construct(private readonly string $instant) {}
    public function now(): DateTimeImmutable { return new DateTimeImmutable($this->instant); }
}

final class SequentialSessionIdGenerator implements SessionIdGeneratorInterface
{
    private int $sequence = 0;
    public function generate(): SessionId
    {
        ++$this->sequence;
        return new SessionId(str_pad((string) $this->sequence, 32, 'x'));
    }
}
