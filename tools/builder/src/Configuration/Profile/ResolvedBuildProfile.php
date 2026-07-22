<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Profile;

use InvalidArgumentException;

final readonly class ResolvedBuildProfile
{
    /**
     * @param list<string> $analyzers
     * @param list<string> $generators
     * @param list<string> $reporters
     */
    public function __construct(
        public string $identifier,
        public array $analyzers,
        public array $generators,
        public array $reporters,
        public bool $strict,
    ) {
        if (trim($identifier) === '') {
            throw new InvalidArgumentException('Resolved build profile identifier cannot be empty.');
        }
    }
}
