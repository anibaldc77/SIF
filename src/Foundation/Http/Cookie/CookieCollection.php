<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use Countable;
use IteratorAggregate;
use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;
use Traversable;

/** @implements IteratorAggregate<string, Cookie> */
final readonly class CookieCollection implements Countable, IteratorAggregate
{
    /** @var array<string, Cookie> */
    private array $cookies;

    /** @param list<Cookie> $cookies */
    public function __construct(array $cookies = [])
    {
        $indexed = [];
        foreach ($cookies as $cookie) {
            $name = $cookie->name()->value();
            if (isset($indexed[$name])) {
                throw new InvalidCookieException(sprintf('Duplicate response cookie "%s".', $name));
            }
            $indexed[$name] = $cookie;
        }
        $this->cookies = $indexed;
    }

    public function has(string $name): bool
    {
        return isset($this->cookies[$name]);
    }

    public function get(string $name): ?Cookie
    {
        return $this->cookies[$name] ?? null;
    }

    public function with(Cookie $cookie): self
    {
        $cookies = $this->cookies;
        $cookies[$cookie->name()->value()] = $cookie;
        return new self(array_values($cookies));
    }

    public function without(string $name): self
    {
        $cookies = $this->cookies;
        unset($cookies[$name]);
        return new self(array_values($cookies));
    }

    /** @return list<string> */
    public function serialized(CookieSerializer $serializer = new CookieSerializer()): array
    {
        $values = [];
        foreach ($this->cookies as $cookie) {
            $values[] = $serializer->serialize($cookie);
        }
        return $values;
    }

    public function count(): int
    {
        return count($this->cookies);
    }

    /** @return Traversable<string, Cookie> */
    public function getIterator(): Traversable
    {
        yield from $this->cookies;
    }
}
