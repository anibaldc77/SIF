<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ContainerDiagnostic
{
    /**
     * @param array<string, scalar|null> $context
     */
    public function __construct(
        private ContainerDiagnosticCode $code,
        private ContainerDiagnosticSeverity $severity,
        private string $message,
        private array $context = [],
    ) {
    }

    public function code(): ContainerDiagnosticCode
    {
        return $this->code;
    }

    public function severity(): ContainerDiagnosticSeverity
    {
        return $this->severity;
    }

    public function message(): string
    {
        return $this->message;
    }

    /**
     * @return array<string, scalar|null>
     */
    public function context(): array
    {
        return $this->context;
    }
}
