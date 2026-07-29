<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Installer;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\Installer\AuthorizedInstallationTarget;
use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Installer\Exceptions\AmbiguousMutationHandlerException;
use Sif\Foundation\Installer\Exceptions\MissingMutationHandlerException;
use Sif\Foundation\Installer\Execution\MutationExecutionResult;
use Sif\Foundation\Installer\Execution\MutationExecutionStatus;
use Sif\Foundation\Installer\Execution\MutationPlanExecutor;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\OverwritePolicy;
use Sif\Foundation\Installer\RollbackPolicy;

final class InstallationExecutionTest extends TestCase
{
    public function testExecutesMutationsInDeclaredOrder(): void
    {
        $events = [];
        $handler = $this->handler($events);
        $plan = new MutationPlan([$this->mutation('first'), $this->mutation('second')]);

        $report = (new MutationPlanExecutor([$handler]))->execute($plan);

        self::assertTrue($report->isSuccessful());
        self::assertSame(['apply:first', 'apply:second'], $events);
        self::assertSame(['applied', 'applied'], $this->statuses($report->summary()));
        self::assertFalse($report->rollbackAttempted());
    }

    public function testFailureTriggersReverseCompensation(): void
    {
        $events = [];
        $handler = $this->handler($events, 'third');
        $plan = new MutationPlan([
            $this->mutation('first'),
            $this->mutation('second'),
            $this->mutation('third'),
        ]);

        $report = (new MutationPlanExecutor([$handler]))->execute($plan);

        self::assertFalse($report->isSuccessful());
        self::assertSame('third', $report->failedMutationIdentifier());
        self::assertTrue($report->rollbackAttempted());
        self::assertTrue($report->rollbackCompleted());
        self::assertSame(
            ['apply:first', 'apply:second', 'apply:third', 'compensate:second', 'compensate:first'],
            $events,
        );
        self::assertSame(
            ['applied', 'applied', 'failed', 'compensated', 'compensated'],
            $this->statuses($report->summary()),
        );
    }

    public function testUnsupportedRollbackIsRecordedWithoutCompensation(): void
    {
        $events = [];
        $handler = $this->handler($events, 'second');
        $plan = new MutationPlan([
            $this->mutation('first', RollbackPolicy::unsupported()),
            $this->mutation('second'),
        ]);

        $report = (new MutationPlanExecutor([$handler]))->execute($plan);

        self::assertFalse($report->rollbackCompleted());
        self::assertSame(['apply:first', 'apply:second'], $events);
        self::assertSame(
            ['applied', 'failed', 'compensation-unsupported'],
            $this->statuses($report->summary()),
        );
    }

    public function testCompensationFailureIsRecordedAndDoesNotStopRemainingRollback(): void
    {
        $events = [];
        $handler = $this->handler($events, 'third', 'second');
        $plan = new MutationPlan([
            $this->mutation('first'),
            $this->mutation('second'),
            $this->mutation('third'),
        ]);

        $report = (new MutationPlanExecutor([$handler]))->execute($plan);

        self::assertFalse($report->rollbackCompleted());
        self::assertSame(
            ['apply:first', 'apply:second', 'apply:third', 'compensate:second', 'compensate:first'],
            $events,
        );
        self::assertSame(
            ['applied', 'applied', 'failed', 'compensation-failed', 'compensated'],
            $this->statuses($report->summary()),
        );
    }

    public function testMissingHandlerFailsBeforeMutation(): void
    {
        $this->expectException(MissingMutationHandlerException::class);
        (new MutationPlanExecutor([]))->execute(new MutationPlan([$this->mutation('first')]));
    }

    public function testAmbiguousHandlerFailsBeforeMutation(): void
    {
        $events = [];
        $this->expectException(AmbiguousMutationHandlerException::class);
        (new MutationPlanExecutor([
            $this->handler($events),
            $this->handler($events),
        ]))->execute(new MutationPlan([$this->mutation('first')]));
    }

    public function testJournalContainsSafeFailureTypeWithoutExceptionMessage(): void
    {
        $events = [];
        $report = (new MutationPlanExecutor([$this->handler($events, 'first')]))
            ->execute(new MutationPlan([$this->mutation('first')]));

        $encoded = json_encode($report->summary(), JSON_THROW_ON_ERROR);
        self::assertStringContainsString(RuntimeException::class, $encoded);
        self::assertStringNotContainsString('sensitive failure detail', $encoded);
        self::assertSame($report->planFingerprint(), (new MutationPlan([$this->mutation('first')]))->fingerprint());
    }

    /** @param list<string> $events */
    private function handler(array &$events, ?string $failApply = null, ?string $failCompensation = null): MutationHandlerInterface
    {
        return new class ($events, $failApply, $failCompensation) implements MutationHandlerInterface {
            /** @param list<string> $events */
            public function __construct(
                private array &$events,
                private readonly ?string $failApply,
                private readonly ?string $failCompensation,
            ) {
            }

            public function supports(MutationDescriptor $mutation): bool
            {
                return true;
            }

            public function apply(MutationDescriptor $mutation): MutationExecutionResult
            {
                $this->record('apply:' . $mutation->identifier());
                if ($mutation->identifier() === $this->failApply) {
                    throw new RuntimeException('sensitive failure detail');
                }

                return new MutationExecutionResult(
                    $mutation->identifier(),
                    MutationExecutionStatus::applied(),
                    hash('sha256', 'receipt:' . $mutation->identifier()),
                );
            }

            public function compensate(
                MutationDescriptor $mutation,
                MutationExecutionResult $appliedResult,
            ): MutationExecutionResult {
                $this->record('compensate:' . $mutation->identifier());
                if ($mutation->identifier() === $this->failCompensation) {
                    throw new RuntimeException('sensitive compensation detail');
                }

                return new MutationExecutionResult(
                    $mutation->identifier(),
                    MutationExecutionStatus::compensated(),
                    hash('sha256', 'compensation:' . $mutation->identifier()),
                    ['applied_receipt' => $appliedResult->receiptFingerprint()],
                );
            }

            private function record(string $event): void
            {
                $events = $this->events;
                $events[] = $event;
                $this->events = $events;
            }
        };
    }

    private function mutation(string $identifier, ?RollbackPolicy $rollbackPolicy = null): MutationDescriptor
    {
        return new MutationDescriptor(
            $identifier,
            'create-file',
            MutationClassification::filesystem(),
            new AuthorizedInstallationTarget('application', 'config/' . $identifier . '.php'),
            OverwritePolicy::deny(),
            $rollbackPolicy ?? RollbackPolicy::compensating(),
            hash('sha256', 'content:' . $identifier),
        );
    }

    /** @param array<string, mixed> $summary
     * @return list<string>
     */
    private function statuses(array $summary): array
    {
        /** @var list<array{result: array{status: string}}> $journal */
        $journal = $summary['journal'];

        return array_map(
            static fn (array $entry): string => $entry['result']['status'],
            $journal,
        );
    }
}
