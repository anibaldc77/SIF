<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Source;

use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnostic;
use Sif\Foundation\Configuration\Diagnostics\ConfigurationDiagnosticSeverity;
use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationSourceDefinitionException;
use Sif\Foundation\Configuration\Exceptions\InvalidEnvironmentConfigurationException;
use Sif\Foundation\Configuration\Source\Contracts\ConfigurationSourceInterface;

final readonly class EnvironmentConfigurationSource implements ConfigurationSourceInterface
{
    /** @var list<EnvironmentVariableDefinition> */
    private array $definitions;

    /** @param iterable<EnvironmentVariableDefinition> $definitions */
    public function __construct(
        private string $sourceId,
        iterable $definitions,
        private int $sourcePrecedence = 100,
    ) {
        if (trim($sourceId) === '') {
            throw InvalidConfigurationSourceDefinitionException::emptyIdentifier();
        }

        $items = [];
        foreach ($definitions as $definition) {
            $items[] = $definition;
        }
        $this->definitions = $items;
    }

    public function id(): string { return $this->sourceId; }
    public function type(): string { return 'environment'; }
    public function precedence(): int { return $this->sourcePrecedence; }

    public function load(): ConfigurationSourceResult
    {
        $values = [];
        $diagnostics = [];

        foreach ($this->definitions as $definition) {
            $raw = getenv($definition->variable());
            if ($raw === false) {
                if ($definition->required()) {
                    throw InvalidEnvironmentConfigurationException::missingRequired($definition->variable());
                }

                if ($definition->default() !== null) {
                    $this->assign($values, $definition->key()->value(), $definition->default());
                }

                $diagnostics[] = new ConfigurationDiagnostic(
                    'CFG_SOURCE_ENV_OPTIONAL_MISSING',
                    ConfigurationDiagnosticSeverity::Warning,
                    'Optional environment variable was not available.',
                    $this->sourceId,
                    ['variable' => $definition->variable(), 'key' => $definition->key()->value()],
                );
                continue;
            }

            $this->assign(
                $values,
                $definition->key()->value(),
                $this->parse($raw, $definition),
            );
        }

        $diagnostics[] = new ConfigurationDiagnostic(
            'CFG_SOURCE_ENV_LOADED',
            ConfigurationDiagnosticSeverity::Info,
            'Environment configuration source loaded successfully.',
            $this->sourceId,
            ['definitions' => count($this->definitions)],
        );

        return new ConfigurationSourceResult(
            $this->sourceId,
            $this->type(),
            $this->sourcePrecedence,
            $values,
            diagnostics: $diagnostics,
        );
    }

    private function parse(string $raw, EnvironmentVariableDefinition $definition): mixed
    {
        return match ($definition->type()) {
            ConfigurationValueType::String => $raw,
            ConfigurationValueType::Integer => filter_var($raw, FILTER_VALIDATE_INT) !== false
                ? (int) $raw
                : throw InvalidEnvironmentConfigurationException::invalidValue($definition->variable(), 'integer'),
            ConfigurationValueType::Float => is_numeric($raw)
                ? (float) $raw
                : throw InvalidEnvironmentConfigurationException::invalidValue($definition->variable(), 'float'),
            ConfigurationValueType::Boolean => $this->parseBoolean($raw, $definition->variable()),
            ConfigurationValueType::Null, ConfigurationValueType::Array => throw InvalidEnvironmentConfigurationException::invalidValue(
                $definition->variable(),
                $definition->type()->value,
            ),
        };
    }

    private function parseBoolean(string $raw, string $variable): bool
    {
        $value = filter_var($raw, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE);
        if ($value === null) {
            throw InvalidEnvironmentConfigurationException::invalidValue($variable, 'boolean');
        }

        return $value;
    }

    /** @param array<array-key, mixed> $values */
    private function assign(array &$values, string $key, mixed $value): void
    {
        $segments = explode('.', $key);
        $cursor = &$values;
        foreach ($segments as $index => $segment) {
            if ($index === array_key_last($segments)) {
                $cursor[$segment] = $value;
                break;
            }
            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }
            $cursor = &$cursor[$segment];
        }
        unset($cursor);
    }
}
