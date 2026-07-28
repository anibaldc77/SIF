<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use InvalidArgumentException;
use Sif\Foundation\ErrorHandling\Contracts\FailureReportFilterInterface;
use Sif\Foundation\ErrorHandling\FailureCategory;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

final readonly class FailureCategoryFilter implements FailureReportFilterInterface
{
    /** @var array<string, true> */
    private array $categories;

    /** @param list<FailureCategory> $categories */
    public function __construct(array $categories)
    {
        if ($categories === []) {
            throw new InvalidArgumentException('At least one failure category is required.');
        }

        $indexed = [];
        foreach ($categories as $category) {
            $indexed[$category->value()] = true;
        }
        $this->categories = $indexed;
    }

    public function accepts(FailureEnvelope $envelope, RecoveryDecision $decision): bool
    {
        return isset($this->categories[$envelope->category()->value()]);
    }
}
