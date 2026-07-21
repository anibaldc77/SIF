<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

use Sif\Builder\Cli\Contract\ComponentCatalogInterface;
use Sif\Builder\Cli\Exception\CliException;

final readonly class StaticComponentCatalog implements ComponentCatalogInterface
{
    /** @var list<string> */
    private array $analyzerIdentifiers;

    /** @var list<string> */
    private array $generatorIdentifiers;

    /** @var list<string> */
    private array $reporterIdentifiers;

    /**
     * @param list<string> $analyzers
     * @param list<string> $generators
     * @param list<string> $reporters
     */
    public function __construct(array $analyzers = [], array $generators = [], array $reporters = [])
    {
        $this->analyzerIdentifiers = self::normalize($analyzers, 'analyzer');
        $this->generatorIdentifiers = self::normalize($generators, 'generator');
        $this->reporterIdentifiers = self::normalize($reporters, 'reporter');
    }

    public function analyzers(): array
    {
        return $this->analyzerIdentifiers;
    }

    public function generators(): array
    {
        return $this->generatorIdentifiers;
    }

    public function reporters(): array
    {
        return $this->reporterIdentifiers;
    }

    /** @param list<string> $identifiers @return list<string> */
    private static function normalize(array $identifiers, string $type): array
    {
        $normalized = [];

        foreach ($identifiers as $identifier) {
            if (!is_string($identifier)) {
                throw new CliException(sprintf('%s identifiers must contain only strings.', ucfirst($type)));
            }

            $identifier = strtolower(trim($identifier));
            if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $identifier)) {
                throw new CliException(sprintf('Invalid %s identifier "%s".', $type, $identifier));
            }
            if (isset($normalized[$identifier])) {
                throw new CliException(sprintf('Duplicate %s identifier "%s".', $type, $identifier));
            }

            $normalized[$identifier] = $identifier;
        }

        return array_values($normalized);
    }
}
