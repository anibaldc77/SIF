<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Http;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Session\Contracts\SessionIdGeneratorInterface;
use Sif\Foundation\Session\Policy\SessionRegenerationPolicy;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\SessionRuntime;
use Sif\Foundation\Session\Storage\InMemorySessionStore;

final class SessionFlashRegenerationExpirationTest extends TestCase
{
    public function testFlashBecomesAvailableOnNextRequestAndThenExpires(): void
    {
        [$runtime] = $this->runtime();
        $first = $runtime->open(null)->state();
        $first->flash('notice', 'saved');
        $runtime->commit($first);

        $second = $runtime->open($first->id()->value())->state();
        self::assertSame('saved', $second->flashGet('notice'));
        $runtime->commit($second);

        $third = $runtime->open($second->id()->value())->state();
        self::assertFalse($third->flashHas('notice'));
    }

    public function testKeepFlashAndReflashPreserveAvailableValues(): void
    {
        [$runtime] = $this->runtime();
        $state = $runtime->open(null)->state();
        $state->flash('one', 1);
        $state->flash('two', 2);
        $runtime->commit($state);

        $next = $runtime->open($state->id()->value())->state();
        $next->keepFlash('one');
        $next->reflash();
        $runtime->commit($next);

        $again = $runtime->open($next->id()->value())->state();
        self::assertSame(['one' => 1, 'two' => 2], $again->flashAll());
    }

    public function testIntervalRegenerationPreservesDataAndInvalidatesPreviousId(): void
    {
        [$runtime, $clock] = $this->runtime(regenerationInterval: 60);
        $state = $runtime->open(null)->state();
        $state->put('user', 7);
        $state->flash('notice', 'ok');
        $runtime->commit($state);
        $old = $state->id()->value();

        $clock->advance(60);
        $loaded = $runtime->open($old)->state();
        $runtime->commit($loaded);

        self::assertNotSame($old, $loaded->id()->value());
        self::assertSame(7, $loaded->get('user'));
        self::assertSame('ok', $loaded->flashGet('notice'));
        self::assertFalse($runtime->open($old)->identifierAccepted());
    }

    public function testAbsoluteAndIdleExpirationUseExactBoundaries(): void
    {
        [$runtime, $clock] = $this->runtime(absolute: 120, idle: 60);
        $state = $runtime->open(null)->state();
        $runtime->commit($state);
        $id = $state->id()->value();

        $clock->advance(60);
        self::assertTrue($runtime->open($id)->expiredRecordDiscarded());
    }

    /** @return array{SessionRuntime, MutableSessionClock} */
    private function runtime(int $absolute = 7200, int $idle = 1800, ?int $regenerationInterval = null): array
    {
        $clock = new MutableSessionClock(new DateTimeImmutable('2030-01-01T00:00:00+00:00'));
        $generator = new FlashSequentialSessionIdGenerator();
        return [
            new SessionRuntime(
                new InMemorySessionStore(),
                $generator,
                $clock,
                new SessionPolicy($absolute, $idle),
                new SessionRegenerationPolicy($regenerationInterval),
            ),
            $clock,
        ];
    }
}

final class MutableSessionClock implements ClockInterface
{
    public function __construct(private DateTimeImmutable $current) {}
    public function now(): DateTimeImmutable { return $this->current; }
    public function advance(int $seconds): void { $this->current = $this->current->modify('+' . $seconds . ' seconds'); }
}

final class FlashSequentialSessionIdGenerator implements SessionIdGeneratorInterface
{
    private int $sequence = 0;
    public function generate(): SessionId
    {
        ++$this->sequence;
        return new SessionId(str_repeat((string) (($this->sequence % 9) + 1), 32));
    }
}
