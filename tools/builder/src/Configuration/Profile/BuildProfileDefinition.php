<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Profile;

use InvalidArgumentException;

final readonly class BuildProfileDefinition
{
    /**
     * @param list<string>|null $analyzers
     * @param list<string>|null $generators
     * @param list<string>|null $reporters
     */
    public function __construct(
        public string $identifier,
        public ?string $extends = null,
        public ?array $analyzers = null,
        public ?array $generators = null,
        public ?array $reporters = null,
        public ?bool $strict = null,
    ) {
        if (preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1) {
            throw new InvalidArgumentException(sprintf('Build profile identifier "%s" is invalid.', $identifier));
        }

        if ($extends !== null && preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $extends) !== 1) {
            throw new InvalidArgumentException(sprintf('Parent build profile identifier "%s" is invalid.', $extends));
        }

        foreach ([$analyzers, $generators, $reporters] as $identifiers) {
            if ($identifiers !== null && count($identifiers) !== count(array_unique($identifiers))) {
                throw new InvalidArgumentException('Build profile extension identifiers must be unique.');
            }
        }
    }
}
