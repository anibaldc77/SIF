<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\GeneratedArtifacts;

use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final readonly class GeneratedArtifactsFinding
{
    /** @param array<string, scalar|null> $context */
    public function __construct(
        public string $code,
        public DiagnosticSeverity $severity,
        public string $message,
        public string $sourcePath,
        public array $context = [],
        public ?string $remediation = null,
    ) {
    }

    public function identity(): string
    {
        return implode('|', [$this->code, $this->sourcePath, $this->message]);
    }
}
