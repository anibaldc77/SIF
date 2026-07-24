<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability;

use Countable;
use IteratorAggregate;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Exceptions\CapabilityNotFoundException;
use Sif\Foundation\Capability\Exceptions\DuplicateCapabilityException;
use Sif\Foundation\Capability\Exceptions\InvalidCapabilityIdentifierException;
use Traversable;

/**
 * In-memory registry for Runtime capabilities.
 *
 * Registration order is preserved. Capability identifiers are unique and are
 * normalized only by trimming surrounding whitespace; their case is retained.
 *
 * @implements IteratorAggregate<string, CapabilityInterface>
 */
final class CapabilityRegistry implements Countable, IteratorAggregate
{
    /** @var array<string, CapabilityInterface> */
    private array $capabilities = [];

    public function register(CapabilityInterface $capability): void
    {
        $identifier = $this->normalizeIdentifier($capability->identifier());

        if (isset($this->capabilities[$identifier])) {
            throw DuplicateCapabilityException::forIdentifier($identifier);
        }

        $this->capabilities[$identifier] = $capability;
    }

    public function has(string $identifier): bool
    {
        $identifier = $this->normalizeIdentifier($identifier);

        return isset($this->capabilities[$identifier]);
    }

    public function get(string $identifier): CapabilityInterface
    {
        $identifier = $this->normalizeIdentifier($identifier);

        return $this->capabilities[$identifier]
            ?? throw CapabilityNotFoundException::forIdentifier($identifier);
    }

    /**
     * Returns all registered capabilities in insertion order.
     *
     * @return list<CapabilityInterface>
     */
    public function all(): array
    {
        return array_values($this->capabilities);
    }

    /**
     * Returns capabilities compatible with the supplied class or interface.
     *
     * @param class-string $type
     *
     * @return list<CapabilityInterface>
     */
    public function ofType(string $type): array
    {
        return array_values(array_filter(
            $this->capabilities,
            static fn (CapabilityInterface $capability): bool => $capability instanceof $type,
        ));
    }

    public function count(): int
    {
        return count($this->capabilities);
    }

    /**
     * @return Traversable<string, CapabilityInterface>
     */
    public function getIterator(): Traversable
    {
        yield from $this->capabilities;
    }

    private function normalizeIdentifier(string $identifier): string
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            throw InvalidCapabilityIdentifierException::empty();
        }

        return $identifier;
    }
}
