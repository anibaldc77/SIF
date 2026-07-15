<?php

declare(strict_types=1);

namespace Sif\Foundation;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Sif\Foundation\Contracts\ServiceProviderInterface;
use Sif\Foundation\Exceptions\DuplicateServiceProviderException;
use Sif\Foundation\Exceptions\ServiceProviderNotFoundException;
use Traversable;

/**
 * Type-safe provider collection preserving insertion order.
 *
 * @implements IteratorAggregate<int, ServiceProviderInterface>
 */
final class ServiceProviderCollection implements IteratorAggregate, Countable
{
    /** @var list<ServiceProviderInterface> */
    private array $providers = [];

    public function add(ServiceProviderInterface $provider): void
    {
        $providerClass = $provider::class;

        if ($this->has($providerClass)) {
            throw DuplicateServiceProviderException::forClass($providerClass);
        }

        $this->providers[] = $provider;
    }

    /** @param class-string<ServiceProviderInterface> $providerClass */
    public function has(string $providerClass): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider::class === $providerClass) {
                return true;
            }
        }

        return false;
    }

    /** @param class-string<ServiceProviderInterface> $providerClass */
    public function get(string $providerClass): ServiceProviderInterface
    {
        foreach ($this->providers as $provider) {
            if ($provider::class === $providerClass) {
                return $provider;
            }
        }

        throw ServiceProviderNotFoundException::forClass($providerClass);
    }

    /** @return list<ServiceProviderInterface> */
    public function all(): array
    {
        return $this->providers;
    }

    /** @return list<ServiceProviderInterface> */
    public function reverse(): array
    {
        return array_reverse($this->providers);
    }

    public function count(): int
    {
        return count($this->providers);
    }

    public function isEmpty(): bool
    {
        return $this->providers === [];
    }

    /** @return Traversable<int, ServiceProviderInterface> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->providers);
    }
}
