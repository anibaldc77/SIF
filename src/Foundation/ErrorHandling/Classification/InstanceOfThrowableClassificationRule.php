<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Classification;

use Sif\Foundation\ErrorHandling\Contracts\ThrowableClassificationRuleInterface;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidClassificationRuleException;
use Throwable;

final readonly class InstanceOfThrowableClassificationRule implements ThrowableClassificationRuleInterface
{
    /** @var class-string<Throwable> */
    private string $throwableType;

    /**
     * @param class-string<Throwable> $throwableType
     */
    public function __construct(
        private string $ruleName,
        string $throwableType,
        private FailureCategory $category,
        private FailureSeverity $severity,
        private FailureDisposition $disposition,
    ) {
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $ruleName)) {
            throw new InvalidClassificationRuleException('Classification rule names must be portable lowercase identifiers.');
        }
        if (!is_a($throwableType, Throwable::class, true)) {
            throw new InvalidClassificationRuleException('Classification rules must target a Throwable type.');
        }
        $this->throwableType = $throwableType;
    }

    public function name(): string
    {
        return $this->ruleName;
    }

    public function classify(Throwable $throwable): ?ThrowableClassification
    {
        if (!$throwable instanceof $this->throwableType) {
            return null;
        }

        return new ThrowableClassification(
            $this->category,
            $this->severity,
            $this->disposition,
            $this->ruleName,
        );
    }
}
