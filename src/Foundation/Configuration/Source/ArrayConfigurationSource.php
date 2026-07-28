<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source;

use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;

final readonly class ArrayConfigurationSource implements ConfigurationSourceInterface
{
    /** @var array<array-key, mixed> */
    private array $values;

    /** @param array<array-key, mixed> $values */
    public function __construct(
        private string $sourceId,
        array $values,
        private int $sourcePrecedence = 0,
        private string $sourceType = 'array',
    ) {
        if (trim($sourceId) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyIdentifier();
        }

        if (trim($sourceType) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyType();
        }

        $this->values = $values;
    }

    public function id(): string
    {
        return $this->sourceId;
    }

    public function type(): string
    {
        return $this->sourceType;
    }

    public function precedence(): int
    {
        return $this->sourcePrecedence;
    }

    public function load(): ConfigurationSourceResult
    {
        return new ConfigurationSourceResult(
            $this->sourceId,
            $this->sourceType,
            $this->sourcePrecedence,
            $this->values,
        );
    }
}
