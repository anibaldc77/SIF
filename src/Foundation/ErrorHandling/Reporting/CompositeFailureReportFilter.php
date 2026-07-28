<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use InvalidArgumentException;
use Sif\Foundation\ErrorHandling\Contracts\FailureReportFilterInterface;
use Sif\Foundation\ErrorHandling\FailureEnvelope;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

final readonly class CompositeFailureReportFilter implements FailureReportFilterInterface
{
    /** @var list<FailureReportFilterInterface> */
    private array $filters;

    /** @param list<FailureReportFilterInterface> $filters */
    public function __construct(array $filters)
    {
        if ($filters === []) {
            throw new InvalidArgumentException('At least one failure report filter is required.');
        }
        $this->filters = array_values($filters);
    }

    public function accepts(FailureEnvelope $envelope, RecoveryDecision $decision): bool
    {
        foreach ($this->filters as $filter) {
            if (!$filter->accepts($envelope, $decision)) {
                return false;
            }
        }

        return true;
    }
}
