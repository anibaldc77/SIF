<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy;

use InvalidArgumentException;
use Sif\Builder\Configuration\Policy\Factory\RequiredCategoryPolicyFactory;
use Sif\Builder\Configuration\Policy\Factory\RequiredMetadataPolicyFactory;

final readonly class RepositoryPolicyFactoryCatalog
{
    /** @var array<string, RepositoryPolicyFactoryInterface> */
    private array $factories;

    /** @param iterable<RepositoryPolicyFactoryInterface> $factories */
    public function __construct(iterable $factories = [])
    {
        $normalized = [];
        foreach ($factories as $factory) {
            $identifier = trim($factory->id());
            if (preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/', $identifier) !== 1) {
                throw new InvalidArgumentException(sprintf('Repository policy factory identifier "%s" is invalid.', $identifier));
            }
            if (isset($normalized[$identifier])) {
                throw new InvalidArgumentException(sprintf('Repository policy factory "%s" is already registered.', $identifier));
            }
            $normalized[$identifier] = $factory;
        }
        ksort($normalized, SORT_STRING);
        $this->factories = $normalized;
    }

    public static function builtIn(): self
    {
        return new self([
            new RequiredCategoryPolicyFactory(),
            new RequiredMetadataPolicyFactory(),
        ]);
    }

    public function has(string $identifier): bool
    {
        return isset($this->factories[$identifier]);
    }

    public function get(string $identifier): RepositoryPolicyFactoryInterface
    {
        return $this->factories[$identifier]
            ?? throw new InvalidArgumentException(sprintf('Repository policy factory "%s" is not registered.', $identifier));
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_keys($this->factories);
    }
}
