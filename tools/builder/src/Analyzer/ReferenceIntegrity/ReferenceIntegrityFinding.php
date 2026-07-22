<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\ReferenceIntegrity;

use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final readonly class ReferenceIntegrityFinding
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public string $code,
        public DiagnosticSeverity $severity,
        public string $message,
        public ?string $sourceIdentifier = null,
        public ?string $sourcePath = null,
        public array $context = [],
        public ?string $remediation = null,
    ) {
    }

    public function identity(): string
    {
        return implode('|', [
            $this->code,
            $this->sourceIdentifier ?? '',
            $this->sourcePath ?? '',
            $this->message,
        ]);
    }
}
