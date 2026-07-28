<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Diagnostics;

use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;

final readonly class ConfigurationDiagnostic
{
    /** @var array<string, bool|float|int|string|null> */
    private array $context;

    /** @param array<string, bool|float|int|string|null> $context */
    public function __construct(
        private string $code,
        private ConfigurationDiagnosticSeverity $severity,
        private string $message,
        private string $sourceId,
        array $context = [],
    ) {
        if (trim($code) === '' || trim($message) === '' || trim($sourceId) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyDiagnosticField();
        }

        $this->context = $context;
    }

    public function code(): string { return $this->code; }
    public function severity(): ConfigurationDiagnosticSeverity { return $this->severity; }
    public function message(): string { return $this->message; }
    public function sourceId(): string { return $this->sourceId; }

    /** @return array<string, bool|float|int|string|null> */
    public function context(): array { return $this->context; }
}
