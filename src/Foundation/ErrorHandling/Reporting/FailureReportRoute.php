<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Reporting;

use InvalidArgumentException;
use Sif\Foundation\ErrorHandling\Contracts\FailureReporterInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureReportFilterInterface;

final readonly class FailureReportRoute
{
    public function __construct(
        private string $name,
        private FailureReportFilterInterface $filter,
        private FailureReporterInterface $reporter,
    ) {
        if (preg_match('/^[a-z0-9]+(?:[.-][a-z0-9]+)*$/', $name) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid failure reporter route name "%s".', $name));
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function filter(): FailureReportFilterInterface
    {
        return $this->filter;
    }

    public function reporter(): FailureReporterInterface
    {
        return $this->reporter;
    }
}
