<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Countable;
use IteratorAggregate;
use Sif\Foundation\Security\Exceptions\InvalidPrincipalAttributeException;
use Traversable;

/** @implements IteratorAggregate<string, PrincipalAttribute> */
final readonly class PrincipalAttributeCollection implements Countable, IteratorAggregate
{
    /** @var array<string, PrincipalAttribute> */
    private array $attributes;

    public function __construct(PrincipalAttribute ...$attributes)
    {
        $indexed = [];

        foreach ($attributes as $attribute) {
            if (isset($indexed[$attribute->name()])) {
                throw new InvalidPrincipalAttributeException(
                    sprintf('Principal attribute "%s" is defined more than once.', $attribute->name())
                );
            }

            $indexed[$attribute->name()] = $attribute;
        }

        ksort($indexed, SORT_STRING);
        $this->attributes = $indexed;
    }

    public function has(string $name): bool
    {
        return isset($this->attributes[$name]);
    }

    public function get(string $name): ?PrincipalAttribute
    {
        return $this->attributes[$name] ?? null;
    }

    /** @return array<string, string|int|float|bool|null> */
    public function toArray(): array
    {
        $result = [];

        foreach ($this->attributes as $name => $attribute) {
            $result[$name] = $attribute->value();
        }

        return $result;
    }

    public function count(): int
    {
        return count($this->attributes);
    }

    /** @return Traversable<string, PrincipalAttribute> */
    public function getIterator(): Traversable
    {
        yield from $this->attributes;
    }
}
