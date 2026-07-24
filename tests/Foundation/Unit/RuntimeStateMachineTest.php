<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Exceptions\InvalidRuntimeTransitionException;
use Sif\Foundation\RuntimeState;
use Sif\Foundation\RuntimeStateMachine;

final class RuntimeStateMachineTest extends TestCase
{
    /**
     * @return iterable<string, array{RuntimeState, RuntimeState}>
     */
    public static function validTransitions(): iterable
    {
        yield 'created to bootstrapping' => [
            RuntimeState::Created,
            RuntimeState::Bootstrapping,
        ];
        yield 'created to failed' => [
            RuntimeState::Created,
            RuntimeState::Failed,
        ];
        yield 'bootstrapping to booted' => [
            RuntimeState::Bootstrapping,
            RuntimeState::Booted,
        ];
        yield 'bootstrapping to failed' => [
            RuntimeState::Bootstrapping,
            RuntimeState::Failed,
        ];
        yield 'booted to running' => [
            RuntimeState::Booted,
            RuntimeState::Running,
        ];
        yield 'booted to stopping' => [
            RuntimeState::Booted,
            RuntimeState::Stopping,
        ];
        yield 'booted to failed' => [
            RuntimeState::Booted,
            RuntimeState::Failed,
        ];
        yield 'running to stopping' => [
            RuntimeState::Running,
            RuntimeState::Stopping,
        ];
        yield 'running to failed' => [
            RuntimeState::Running,
            RuntimeState::Failed,
        ];
        yield 'stopping to stopped' => [
            RuntimeState::Stopping,
            RuntimeState::Stopped,
        ];
        yield 'stopping to failed' => [
            RuntimeState::Stopping,
            RuntimeState::Failed,
        ];
    }

    #[DataProvider('validTransitions')]
    public function testAcceptsEveryApprovedTransition(
        RuntimeState $from,
        RuntimeState $to,
    ): void {
        $stateMachine = new RuntimeStateMachine();

        self::assertTrue($stateMachine->canTransition($from, $to));
        $stateMachine->assertCanTransition($from, $to);
        self::assertTrue(true);
    }

    public function testReturnsApprovedTransitionsForBootedState(): void
    {
        $stateMachine = new RuntimeStateMachine();

        self::assertSame(
            [
                RuntimeState::Running,
                RuntimeState::Stopping,
                RuntimeState::Failed,
            ],
            $stateMachine->allowedTransitions(RuntimeState::Booted),
        );
    }

    public function testTerminalStatesExposeNoTransitions(): void
    {
        $stateMachine = new RuntimeStateMachine();

        self::assertSame(
            [],
            $stateMachine->allowedTransitions(RuntimeState::Stopped),
        );
        self::assertSame(
            [],
            $stateMachine->allowedTransitions(RuntimeState::Failed),
        );
    }

    public function testRejectsTransitionOutsideApprovedGraph(): void
    {
        $stateMachine = new RuntimeStateMachine();

        self::assertFalse(
            $stateMachine->canTransition(
                RuntimeState::Created,
                RuntimeState::Running,
            ),
        );

        $this->expectException(InvalidRuntimeTransitionException::class);
        $this->expectExceptionMessage(
            'Invalid runtime transition from created to running.',
        );

        $stateMachine->assertCanTransition(
            RuntimeState::Created,
            RuntimeState::Running,
        );
    }
}
