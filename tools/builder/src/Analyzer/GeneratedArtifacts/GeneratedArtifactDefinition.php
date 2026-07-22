<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\GeneratedArtifacts;

use InvalidArgumentException;

final readonly class GeneratedArtifactDefinition
{
    public string $generator;
    public string $relativePath;

    public function __construct(string $generator, string $relativePath)
    {
        $generator = strtolower(trim($generator));
        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $generator)) {
            throw new InvalidArgumentException(sprintf('Generated artifact generator "%s" is invalid.', $generator));
        }

        $relativePath = trim(str_replace('\\', '/', $relativePath));
        if ($relativePath === '' || str_starts_with($relativePath, '/') || preg_match('/^[A-Za-z]:\//', $relativePath)) {
            throw new InvalidArgumentException('Generated artifact path must be relative.');
        }
        foreach (explode('/', $relativePath) as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidArgumentException('Generated artifact path contains an invalid segment.');
            }
        }

        $this->generator = $generator;
        $this->relativePath = $relativePath;
    }
}
