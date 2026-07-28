<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source;

use Sif\Foundation\Configuration\ConfigurationValueValidator;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;

final readonly class ConfigurationSourceResult
{
    /** @var array<array-key, mixed> */
    private array $values;

    /** @var list<ConfigurationDiagnostic> */
    private array $diagnostics;

    /**
     * @param array<array-key, mixed> $values
     * @param list<ConfigurationDiagnostic> $diagnostics
     */
    public function __construct(
        private string $sourceId,
        private string $sourceType,
        private int $precedence,
        array $values,
        ?ConfigurationValueValidator $validator = null,
        array $diagnostics = [],
    ) {
        if (trim($sourceId) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyIdentifier();
        }

        if (trim($sourceType) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyType();
        }

        ($validator ?? new ConfigurationValueValidator())->assertSupported($values);
        $this->values = $values;
        $this->diagnostics = array_values($diagnostics);
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function sourceType(): string
    {
        return $this->sourceType;
    }

    public function precedence(): int
    {
        return $this->precedence;
    }

    /** @return array<array-key, mixed> */
    public function values(): array
    {
        return $this->values;
    }

    /** @return list<ConfigurationDiagnostic> */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }
}
