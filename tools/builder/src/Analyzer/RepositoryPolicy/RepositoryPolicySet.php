<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy;

use InvalidArgumentException;

final readonly class RepositoryPolicySet
{
    /** @var list<RepositoryPolicyRuleInterface> */
    private array $rules;

    /** @param iterable<RepositoryPolicyRuleInterface> $rules */
    public function __construct(iterable $rules = [])
    {
        $normalized = [];
        foreach ($rules as $rule) {
            $identifier = trim($rule->id());
            if ($identifier === '' || preg_match('/^[a-z][a-z0-9]*(?:-[a-z0-9]+)*(?:\.[a-z][a-z0-9]*(?:-[a-z0-9]+)*)*$/', $identifier) !== 1) {
                throw new InvalidArgumentException(sprintf('Repository policy identifier "%s" is invalid.', $identifier));
            }

            if (isset($normalized[$identifier])) {
                throw new InvalidArgumentException(sprintf('Repository policy "%s" is already registered.', $identifier));
            }

            $normalized[$identifier] = $rule;
        }

        ksort($normalized, SORT_STRING);
        $this->rules = array_values($normalized);
    }

    /** @return list<RepositoryPolicyRuleInterface> */
    public function all(): array
    {
        return $this->rules;
    }

    public function isEmpty(): bool
    {
        return $this->rules === [];
    }
}
