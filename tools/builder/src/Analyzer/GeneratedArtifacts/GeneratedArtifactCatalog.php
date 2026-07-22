<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\GeneratedArtifacts;

use InvalidArgumentException;

final readonly class GeneratedArtifactCatalog
{
    /** @var list<GeneratedArtifactDefinition> */
    private array $definitions;

    /** @param iterable<GeneratedArtifactDefinition> $definitions */
    public function __construct(iterable $definitions = [])
    {
        $indexed = [];
        foreach ($definitions as $definition) {
            if (isset($indexed[$definition->relativePath])) {
                throw new InvalidArgumentException(sprintf(
                    'Generated artifact path "%s" is registered more than once.',
                    $definition->relativePath,
                ));
            }
            $indexed[$definition->relativePath] = $definition;
        }

        ksort($indexed);
        $this->definitions = array_values($indexed);
    }

    public static function builtIn(): self
    {
        return new self([
            new GeneratedArtifactDefinition('documentation.navigation', 'engineering/NAVIGATION.generated.md'),
            new GeneratedArtifactDefinition('reference.graph', 'build/reference-graph.generated.json'),
            new GeneratedArtifactDefinition('reference.report', 'engineering/REFERENCES.generated.md'),
            new GeneratedArtifactDefinition('repository.index', 'engineering/INDEX.generated.md'),
            new GeneratedArtifactDefinition('repository.manifest', 'build/repository-manifest.generated.json'),
        ]);
    }

    /** @return list<GeneratedArtifactDefinition> */
    public function all(): array
    {
        return $this->definitions;
    }

    /** @return list<string> */
    public function paths(): array
    {
        return array_map(
            static fn (GeneratedArtifactDefinition $definition): string => $definition->relativePath,
            $this->definitions,
        );
    }
}
