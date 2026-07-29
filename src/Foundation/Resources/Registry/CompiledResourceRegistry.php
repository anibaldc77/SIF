<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Registry;

use Sif\Foundation\Resources\Contracts\ResourceRegistryInterface;
use Sif\Foundation\Resources\Exceptions\DuplicateResourceException;
use Sif\Foundation\Resources\Exceptions\ResourceNotFoundException;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;

final readonly class CompiledResourceRegistry implements ResourceRegistryInterface
{
    /** @var array<string, RegisteredResource> */
    private array $entriesByKey;

    /** @var list<RegisteredResource> */
    private array $orderedEntries;

    /**
     * @param list<RegisteredResource> $entries
     */
    public function __construct(array $entries)
    {
        $entriesByKey = [];
        foreach ($entries as $entry) {
            if (isset($entriesByKey[$entry->key()])) {
                throw new DuplicateResourceException(sprintf('Resource "%s" occurs more than once in the compiled registry.', $entry->key()));
            }
            $entriesByKey[$entry->key()] = $entry;
        }

        $this->entriesByKey = $entriesByKey;
        $this->orderedEntries = array_values($entries);
    }

    public function has(ResourceNamespace $namespace, ResourceIdentifier $identifier): bool
    {
        return isset($this->entriesByKey[self::key($namespace, $identifier)]);
    }

    public function get(ResourceNamespace $namespace, ResourceIdentifier $identifier): ResourceDescriptor
    {
        $key = self::key($namespace, $identifier);
        if (!isset($this->entriesByKey[$key])) {
            throw new ResourceNotFoundException(sprintf('Resource "%s" is not registered.', $key));
        }

        return $this->entriesByKey[$key]->descriptor();
    }

    public function entries(): array
    {
        return $this->orderedEntries;
    }

    public function resources(): array
    {
        return array_map(
            static fn (RegisteredResource $entry): ResourceDescriptor => $entry->descriptor(),
            $this->orderedEntries,
        );
    }

    public function count(): int
    {
        return count($this->orderedEntries);
    }

    private static function key(ResourceNamespace $namespace, ResourceIdentifier $identifier): string
    {
        return $namespace->value() . ':' . $identifier->value();
    }
}
