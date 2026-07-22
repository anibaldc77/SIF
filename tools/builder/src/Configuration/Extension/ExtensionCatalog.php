<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Extension;

use InvalidArgumentException;

final readonly class ExtensionCatalog
{
    /**
     * @param list<string> $analyzers
     * @param list<string> $generators
     * @param list<string> $reporters
     */
    public function __construct(
        public array $analyzers,
        public array $generators,
        public array $reporters,
    ) {
        $this->assertIdentifiers($analyzers, 'analyzer');
        $this->assertIdentifiers($generators, 'generator');
        $this->assertIdentifiers($reporters, 'reporter');
    }

    public static function builtInDefault(): self
    {
        return new self(
            analyzers: [
                'metadata.completeness',
                'reference.integrity',
                'document.consistency',
                'repository.policy',
                'generated.artifacts',
            ],
            generators: [
                'repository.index',
                'reference.report',
                'reference.graph',
                'repository.manifest',
                'documentation.navigation',
            ],
            reporters: [
                'report.markdown',
                'report.json',
            ],
        );
    }

    public function hasAnalyzer(string $identifier): bool
    {
        return in_array($identifier, $this->analyzers, true);
    }

    public function hasGenerator(string $identifier): bool
    {
        return in_array($identifier, $this->generators, true);
    }

    public function hasReporter(string $identifier): bool
    {
        return in_array($identifier, $this->reporters, true);
    }

    /** @param list<string> $identifiers */
    private function assertIdentifiers(array $identifiers, string $category): void
    {
        $seen = [];
        foreach ($identifiers as $identifier) {
            if (preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1) {
                throw new InvalidArgumentException(sprintf(
                    'Extension %s identifier "%s" is invalid.',
                    $category,
                    $identifier,
                ));
            }

            if (in_array($identifier, $seen, true)) {
                throw new InvalidArgumentException(sprintf(
                    'Extension %s identifier "%s" is duplicated.',
                    $category,
                    $identifier,
                ));
            }

            $seen[] = $identifier;
        }
    }
}
