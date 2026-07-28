<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Exceptions\DuplicateRecoveryPolicyException;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidRecoveryDecisionException;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\Recovery\DispositionRecoveryPolicy;
use Sif\Foundation\ErrorHandling\Recovery\ExponentialRetryDelayStrategy;
use Sif\Foundation\ErrorHandling\Recovery\FixedRetryDelayStrategy;
use Sif\Foundation\ErrorHandling\Recovery\OrderedRecoveryDecider;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryAction;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;
use Sif\Foundation\ErrorHandling\Recovery\RetryGuidance;
use Sif\Foundation\ErrorHandling\Recovery\TransientRetryRecoveryPolicy;

final class RecoveryDecisionTest extends TestCase
{
    public function testRecoveryActionVocabularyIsCanonical(): void
    {
        self::assertSame('retry', RecoveryAction::retry()->value());
        self::assertTrue(RecoveryAction::retry()->isRetry());
        self::assertFalse(RecoveryAction::abort()->isRetry());
    }

    public function testRetryDecisionRequiresGuidance(): void
    {
        $this->expectException(InvalidRecoveryDecisionException::class);
        new RecoveryDecision(RecoveryAction::retry(), 'retry.transient');
    }

    public function testNonRetryDecisionRejectsGuidance(): void
    {
        $this->expectException(InvalidRecoveryDecisionException::class);
        new RecoveryDecision(RecoveryAction::abort(), 'abort.invalid', new RetryGuidance(1, 2, 0));
    }

    public function testFixedDelayIsDeterministic(): void
    {
        $strategy = new FixedRetryDelayStrategy(250);
        self::assertSame(250, $strategy->delayMilliseconds(1));
        self::assertSame(250, $strategy->delayMilliseconds(5));
    }

    public function testExponentialDelayIsBounded(): void
    {
        $strategy = new ExponentialRetryDelayStrategy(100, 500);
        self::assertSame(100, $strategy->delayMilliseconds(1));
        self::assertSame(400, $strategy->delayMilliseconds(3));
        self::assertSame(500, $strategy->delayMilliseconds(5));
    }

    public function testTransientPolicyReturnsRetryBeforeAttemptLimit(): void
    {
        $decision = $this->retryPolicy()->decide($this->classification(FailureDisposition::transient()), 2);
        self::assertNotNull($decision);
        self::assertSame('retry', $decision->action()->value());
        self::assertSame(200, $decision->retryGuidance()?->delayMilliseconds());
    }

    public function testTransientPolicyReturnsExhaustedActionAtLimit(): void
    {
        $decision = $this->retryPolicy()->decide($this->classification(FailureDisposition::transient()), 3);
        self::assertNotNull($decision);
        self::assertSame('rethrow', $decision->action()->value());
        self::assertNull($decision->retryGuidance());
    }

    public function testFirstMatchingRecoveryPolicyWins(): void
    {
        $decider = OrderedRecoveryDecider::withRethrowFallback([
            new DispositionRecoveryPolicy('transient.degrade', FailureDisposition::transient(), RecoveryAction::degrade()),
            $this->retryPolicy(),
        ]);
        self::assertSame('transient.degrade', $decider->decide($this->classification(FailureDisposition::transient()))->policy());
    }

    public function testUnknownClassificationUsesExplicitFallback(): void
    {
        $decision = OrderedRecoveryDecider::withRethrowFallback([])
            ->decide($this->classification(FailureDisposition::unknown()));
        self::assertSame('rethrow', $decision->action()->value());
        self::assertTrue($decision->isFallback());
    }

    public function testDuplicatePolicyNamesAreRejected(): void
    {
        $this->expectException(DuplicateRecoveryPolicyException::class);
        OrderedRecoveryDecider::withRethrowFallback([$this->retryPolicy(), $this->retryPolicy()]);
    }

    public function testDecisionSummaryIsCanonical(): void
    {
        $decision = new RecoveryDecision(
            RecoveryAction::retry(),
            'transient.retry',
            new RetryGuidance(1, 3, 100),
        );
        self::assertSame([
            'action' => 'retry',
            'policy' => 'transient.retry',
            'fallback' => false,
            'retry' => [
                'attempt' => 1,
                'maximum_attempts' => 3,
                'delay_milliseconds' => 100,
                'remaining' => true,
            ],
        ], $decision->summary());
    }

    private function retryPolicy(): TransientRetryRecoveryPolicy
    {
        return new TransientRetryRecoveryPolicy('transient.retry', 3, new ExponentialRetryDelayStrategy(100, 500));
    }

    private function classification(FailureDisposition $disposition): ThrowableClassification
    {
        return new ThrowableClassification(
            FailureCategory::infrastructure(),
            FailureSeverity::error(),
            $disposition,
            'test.rule',
        );
    }
}
