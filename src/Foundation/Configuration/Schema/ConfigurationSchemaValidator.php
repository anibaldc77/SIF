<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema;

use Sif\Foundation\Configuration\ConfigurationValueType;
use Sif\Foundation\Configuration\Contracts\TypedConfigurationInterface;
use Sif\Foundation\Configuration\ImmutableConfigurationRepository;
use Sif\Foundation\Configuration\Schema\Contracts\ConfigurationSchemaInterface;

final readonly class ConfigurationSchemaValidator
{
    public function validate(
        TypedConfigurationInterface $configuration,
        ConfigurationSchemaInterface $schema,
    ): ConfigurationValidationResult {
        $values = $configuration->all();
        $issues = [];

        foreach ($schema->rules() as $rule) {
            $lookup = $configuration->lookup($rule->key);

            if ($lookup->isMissing()) {
                if ($rule->required) {
                    $issues[] = new ConfigurationValidationIssue(
                        'CFG_SCHEMA_REQUIRED_KEY_MISSING',
                        sprintf('Required configuration key "%s" is missing.', $rule->key->value()),
                        $rule->key,
                    );
                }

                continue;
            }

            $value = $lookup->value();

            if ($value === null && $rule->nullable) {
                continue;
            }

            if ($rule->normalizer !== null) {
                $value = $rule->normalizer->normalize($value);
                $this->writeValue($values, $rule->key->segments(), $value);
            }

            $actualType = ConfigurationValueType::fromValue($value);

            if ($actualType !== $rule->type) {
                $issues[] = new ConfigurationValidationIssue(
                    'CFG_SCHEMA_TYPE_MISMATCH',
                    sprintf(
                        'Configuration key "%s" must be of type %s; %s given.',
                        $rule->key->value(),
                        $rule->type->value,
                        $actualType->value,
                    ),
                    $rule->key,
                );
            }
        }

        return new ConfigurationValidationResult(
            new ImmutableConfigurationRepository($values),
            $issues,
        );
    }

    /**
     * @param array<array-key, mixed> $values
     * @param non-empty-list<string> $segments
     */
    private function writeValue(array &$values, array $segments, mixed $value): void
    {
        $cursor = &$values;
        $last = array_pop($segments);

        foreach ($segments as $segment) {
            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                $cursor[$segment] = [];
            }

            /** @var array<array-key, mixed> $next */
            $next = &$cursor[$segment];
            $cursor = &$next;
        }

        $cursor[$last] = $value;
    }
}
