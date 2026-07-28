<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema;

use Sif\Foundation\Configuration\Schema\Contracts\ConfigurationSchemaInterface;

final readonly class ConfigurationSchema implements ConfigurationSchemaInterface
{
    /** @var list<ConfigurationSchemaRule> */
    private array $rules;

    /** @param list<ConfigurationSchemaRule> $rules */
    public function __construct(array $rules)
    {
        $seen = [];

        foreach ($rules as $rule) {
            $key = $rule->key->value();

            if (isset($seen[$key])) {
                throw new \InvalidArgumentException(sprintf('Duplicate configuration schema rule for key "%s".', $key));
            }

            $seen[$key] = true;
        }

        $this->rules = array_values($rules);
    }

    public function rules(): array
    {
        return $this->rules;
    }
}
