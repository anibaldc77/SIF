<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Classification;

use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureDisposition;
use Sif\Foundation\ErrorHandling\FailureSeverity;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidClassificationRuleException;

final readonly class ThrowableClassification
{
    public function __construct(
        private FailureCategory $category,
        private FailureSeverity $severity,
        private FailureDisposition $disposition,
        private string $rule,
        private bool $fallback = false,
    ) {
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $rule)) {
            throw new InvalidClassificationRuleException('Classification rule names must be portable lowercase identifiers.');
        }
    }

    public function category(): FailureCategory
    {
        return $this->category;
    }

    public function severity(): FailureSeverity
    {
        return $this->severity;
    }

    public function disposition(): FailureDisposition
    {
        return $this->disposition;
    }

    public function rule(): string
    {
        return $this->rule;
    }

    public function isFallback(): bool
    {
        return $this->fallback;
    }

    /** @return array{category:string,severity:string,disposition:string,rule:string,fallback:bool} */
    public function summary(): array
    {
        return [
            'category' => $this->category->value(),
            'severity' => $this->severity->value(),
            'disposition' => $this->disposition->value(),
            'rule' => $this->rule,
            'fallback' => $this->fallback,
        ];
    }
}
