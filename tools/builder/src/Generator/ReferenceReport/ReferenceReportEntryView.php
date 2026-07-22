<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\ReferenceReport;

final readonly class ReferenceReportEntryView
{
    public function __construct(
        public string $source,
        public string $target,
        public string $type,
        public string $status,
        public ?int $line = null,
        public ?string $reason = null,
    ) {
    }
}
