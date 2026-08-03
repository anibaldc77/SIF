<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Sif\Foundation\Http\Exceptions\InvalidHttpRequestException;
use Traversable;

/** @implements IteratorAggregate<string, mixed> */
abstract class AbstractParameterBag implements IteratorAggregate, Countable
{
    /** @var array<string, mixed> */
    private array $values;

    /** @param array<string, mixed> $values */
    final public function __construct(array $values = [])
    {
        foreach (array_keys($values) as $name) {
            self::assertName($name);
        }
        $this->values = $values;
    }

    final public function has(string $name): bool
    {
        self::assertName($name);
        return array_key_exists($name, $this->values);
    }

    final public function get(string $name, mixed $default = null): mixed
    {
        self::assertName($name);
        return $this->values[$name] ?? $default;
    }

    /** @return array<string, mixed> */
    final public function all(): array
    {
        return $this->values;
    }

    final public function with(string $name, mixed $value): static
    {
        self::assertName($name);
        $values = $this->values;
        $values[$name] = $value;
        return new static($values);
    }

    final public function without(string $name): static
    {
        self::assertName($name);
        $values = $this->values;
        unset($values[$name]);
        return new static($values);
    }

    final public function count(): int
    {
        return count($this->values);
    }

    /** @return Traversable<string, mixed> */
    final public function getIterator(): Traversable
    {
        return new ArrayIterator($this->values);
    }

    private static function assertName(string $name): void
    {
        if ($name === '' || preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
            throw new InvalidHttpRequestException('HTTP parameter names must be non-empty and contain no control characters.');
        }
    }
}
