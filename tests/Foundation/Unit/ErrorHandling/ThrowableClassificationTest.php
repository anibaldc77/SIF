<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\ErrorHandling;

use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Sif\Foundation\ErrorHandling\Classification\FallbackThrowableClassification;
use Sif\Foundation\ErrorHandling\Classification\InstanceOfThrowableClassificationRule;
use Sif\Foundation\ErrorHandling\Classification\OrderedThrowableClassifier;
use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Exceptions\DuplicateClassificationRuleException;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidClassificationRuleException;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureSeverity;

final class ThrowableClassificationTest extends TestCase
{
    public function testInstanceOfRuleClassifiesMatchingThrowable(): void
    {
        $rule = $this->rule('validation.invalid-argument', InvalidArgumentException::class);
        $classification = $rule->classify(new InvalidArgumentException('Invalid'));

        self::assertNotNull($classification);
        self::assertSame('validation', $classification->category()->value());
        self::assertSame('invalid', $classification->disposition()->value());
        self::assertSame('validation.invalid-argument', $classification->rule());
    }

    public function testInstanceOfRuleReturnsNullForNonMatchingThrowable(): void
    {
        self::assertNull($this->rule('validation.invalid-argument', InvalidArgumentException::class)
            ->classify(new RuntimeException('Runtime')));
    }

    public function testFirstMatchingRuleWinsDeterministically(): void
    {
        $classifier = new OrderedThrowableClassifier([
            $this->rule('logic.general', LogicException::class, FailureCategory::application()),
            $this->rule('logic.invalid-argument', InvalidArgumentException::class, FailureCategory::configuration()),
        ], FallbackThrowableClassification::unknown());

        $classification = $classifier->classify(new InvalidArgumentException('Invalid'));

        self::assertSame('logic.general', $classification->rule());
        self::assertSame('application', $classification->category()->value());
    }

    public function testSpecificRuleCanTakePriorityWhenDeclaredFirst(): void
    {
        $classifier = new OrderedThrowableClassifier([
            $this->rule('logic.invalid-argument', InvalidArgumentException::class, FailureCategory::configuration()),
            $this->rule('logic.general', LogicException::class, FailureCategory::application()),
        ], FallbackThrowableClassification::unknown());

        self::assertSame(
            'logic.invalid-argument',
            $classifier->classify(new InvalidArgumentException('Invalid'))->rule(),
        );
    }

    public function testUnknownThrowableUsesExplicitFallback(): void
    {
        $classification = OrderedThrowableClassifier::withUnknownFallback([])
            ->classify(new RuntimeException('Unknown'));

        self::assertTrue($classification->isFallback());
        self::assertSame('unknown', $classification->category()->value());
        self::assertSame('error', $classification->severity()->value());
        self::assertSame('unknown', $classification->disposition()->value());
    }

    public function testRuleOrderIsInspectableAndStable(): void
    {
        $classifier = OrderedThrowableClassifier::withUnknownFallback([
            $this->rule('first.rule', InvalidArgumentException::class),
            $this->rule('second.rule', RuntimeException::class),
        ]);

        self::assertSame(['first.rule', 'second.rule'], $classifier->ruleNames());
    }

    public function testDuplicateRuleNamesAreRejected(): void
    {
        $this->expectException(DuplicateClassificationRuleException::class);
        OrderedThrowableClassifier::withUnknownFallback([
            $this->rule('same.rule', InvalidArgumentException::class),
            $this->rule('same.rule', RuntimeException::class),
        ]);
    }

    public function testInvalidRuleNameIsRejected(): void
    {
        $this->expectException(InvalidClassificationRuleException::class);
        $this->rule('Invalid Rule', RuntimeException::class);
    }

    public function testNonThrowableTargetIsRejected(): void
    {
        $this->expectException(InvalidClassificationRuleException::class);
        // @phpstan-ignore argument.type
        $this->rule('invalid.target', \stdClass::class);
    }

    public function testClassificationSummaryIsCanonical(): void
    {
        $classification = new ThrowableClassification(
            FailureCategory::dependency(),
            FailureSeverity::critical(),
            FailureDisposition::transient(),
            'dependency.unavailable',
        );

        self::assertSame([
            'category' => 'dependency',
            'severity' => 'critical',
            'disposition' => 'transient',
            'rule' => 'dependency.unavailable',
            'fallback' => false,
        ], $classification->summary());
    }

    /** @param class-string<\Throwable> $type */
    private function rule(
        string $name,
        string $type,
        ?FailureCategory $category = null,
    ): InstanceOfThrowableClassificationRule {
        return new InstanceOfThrowableClassificationRule(
            $name,
            $type,
            $category ?? FailureCategory::validation(),
            FailureSeverity::error(),
            FailureDisposition::invalid(),
        );
    }
}
