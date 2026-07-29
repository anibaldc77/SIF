<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Registry;

use Sif\Foundation\Resources\Contracts\MutableResourceRegistryInterface;
use Sif\Foundation\Resources\Exceptions\DuplicateResourceException;
use Sif\Foundation\Resources\Exceptions\ResourceNotFoundException;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;

final class ResourceRegistry implements MutableResourceRegistryInterface
{
    /** @var array<string, RegisteredResource> */
    private array $entries = [];

    private int $nextRegistrationOrder = 0;

    public function register(ResourceDescriptor $descriptor): RegisteredResource
    {
        $key = $descriptor->qualifiedIdentifier();
        if (isset($this->entries[$key])) {
            throw new DuplicateResourceException(sprintf('Resource "%s" is already registered.', $key));
        }

        $entry = new RegisteredResource($descriptor, $this->nextRegistrationOrder++);
        $this->entries[$key] = $entry;

        return $entry;
    }

    public function has(ResourceNamespace $namespace, ResourceIdentifier $identifier): bool
    {
        return isset($this->entries[self::key($namespace, $identifier)]);
    }

    public function get(ResourceNamespace $namespace, ResourceIdentifier $identifier): ResourceDescriptor
    {
        $key = self::key($namespace, $identifier);
        if (!isset($this->entries[$key])) {
            throw new ResourceNotFoundException(sprintf('Resource "%s" is not registered.', $key));
        }

        return $this->entries[$key]->descriptor();
    }

    public function entries(): array
    {
        return self::orderedEntries(array_values($this->entries));
    }

    public function resources(): array
    {
        return array_map(
            static fn (RegisteredResource $entry): ResourceDescriptor => $entry->descriptor(),
            $this->entries(),
        );
    }

    public function count(): int
    {
        return count($this->entries);
    }

    public function compile(): CompiledResourceRegistry
    {
        return new CompiledResourceRegistry($this->entries());
    }

    private static function key(ResourceNamespace $namespace, ResourceIdentifier $identifier): string
    {
        return $namespace->value() . ':' . $identifier->value();
    }

    /**
     * @param list<RegisteredResource> $entries
     * @return list<RegisteredResource>
     */
    private static function orderedEntries(array $entries): array
    {
        usort(
            $entries,
            static function (RegisteredResource $left, RegisteredResource $right): int {
                $priority = $right->descriptor()->priority()->compare($left->descriptor()->priority());
                if ($priority !== 0) {
                    return $priority;
                }

                return $left->registrationOrder() <=> $right->registrationOrder();
            },
        );

        return $entries;
    }
}
