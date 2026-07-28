<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source;

use Sif\Foundation\Configuration\ConfigurationKey;
use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;

final readonly class EnvironmentVariableDefinition
{
    public function __construct(
        private string $variable,
        string $configurationKey,
        private ConfigurationValueType $type = ConfigurationValueType::String,
        private bool $required = false,
        private mixed $default = null,
    ) {
        if (trim($variable) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyIdentifier();
        }

        $this->key = new ConfigurationKey($configurationKey);
    }

    private ConfigurationKey $key;

    public function variable(): string { return $this->variable; }
    public function key(): ConfigurationKey { return $this->key; }
    public function type(): ConfigurationValueType { return $this->type; }
    public function required(): bool { return $this->required; }
    public function default(): mixed { return $this->default; }
}
