<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\MetadataCompleteness;

use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final readonly class MetadataCompletenessFinding
{
    /** @param array<string, bool|float|int|string|null> $context */
    public function __construct(
        public string $code,
        public DiagnosticSeverity $severity,
        public string $message,
        public string $path,
        public string $documentIdentifier,
        public string $field,
        public array $context = [],
        public ?string $remediation = null,
    ) {
    }

    public function identity(): string
    {
        return implode('|', [
            str_pad((string) (100 - $this->severity->value), 3, '0', STR_PAD_LEFT),
            $this->code,
            $this->path,
            $this->documentIdentifier,
            $this->field,
            $this->message,
        ]);
    }
}
