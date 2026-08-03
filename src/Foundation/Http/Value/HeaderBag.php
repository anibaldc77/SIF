<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use ArrayIterator;
use Sif\Foundation\Contracts\HeaderBagInterface;
use Sif\Foundation\Http\Exceptions\InvalidHttpHeaderException;
use Traversable;

final class HeaderBag implements HeaderBagInterface
{
    /** @var array<string, array{name: string, values: list<string>}> */
    private array $headers;

    /** @param array<string, string|list<string>> $headers */
    public function __construct(array $headers = [])
    {
        $normalized = [];
        foreach ($headers as $name => $values) {
            $key = self::normalizeName($name);
            if (isset($normalized[$key])) {
                throw new InvalidHttpHeaderException(sprintf('Duplicate HTTP header "%s".', $name));
            }
            $normalized[$key] = ['name' => $name, 'values' => self::normalizeValues($values)];
        }
        $this->headers = $normalized;
    }

    public function has(string $name): bool
    {
        return isset($this->headers[self::normalizeName($name)]);
    }

    public function values(string $name): array
    {
        return $this->headers[self::normalizeName($name)]['values'] ?? [];
    }

    public function line(string $name): string
    {
        return implode(', ', $this->values($name));
    }

    public function all(): array
    {
        $result = [];
        foreach ($this->headers as $header) {
            $result[$header['name']] = $header['values'];
        }
        return $result;
    }

    public function with(string $name, string|array $values): self
    {
        $headers = $this->headers;
        $key = self::normalizeName($name);
        $headers[$key] = ['name' => $headers[$key]['name'] ?? $name, 'values' => self::normalizeValues($values)];
        return self::fromNormalized($headers);
    }

    public function withAdded(string $name, string|array $values): self
    {
        $headers = $this->headers;
        $key = self::normalizeName($name);
        $additional = self::normalizeValues($values);
        if (isset($headers[$key])) {
            $headers[$key]['values'] = [...$headers[$key]['values'], ...$additional];
        } else {
            $headers[$key] = ['name' => $name, 'values' => $additional];
        }
        return self::fromNormalized($headers);
    }

    public function without(string $name): self
    {
        $headers = $this->headers;
        unset($headers[self::normalizeName($name)]);
        return self::fromNormalized($headers);
    }

    public function count(): int
    {
        return count($this->headers);
    }

    /** @return Traversable<string, list<string>> */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->all());
    }

    /** @param array<string, array{name: string, values: list<string>}> $headers */
    private static function fromNormalized(array $headers): self
    {
        $instance = new self();
        $instance->headers = $headers;
        return $instance;
    }

    private static function normalizeName(string $name): string
    {
        if ($name === '' || preg_match("/^[!#$%&'*+.^_`|~0-9A-Za-z-]+$/", $name) !== 1) {
            throw new InvalidHttpHeaderException(sprintf('Invalid HTTP header name "%s".', $name));
        }
        return strtolower($name);
    }

    /**
     * @param string|list<string> $values
     *
     * @return list<string>
     */
    private static function normalizeValues(string|array $values): array
    {
        $normalized = is_string($values) ? [$values] : array_values($values);
        if ($normalized === []) {
            throw new InvalidHttpHeaderException('HTTP header values must not be empty.');
        }
        foreach ($normalized as $value) {
            if (preg_match('/[\r\n\x00]/', $value) === 1) {
                throw new InvalidHttpHeaderException('HTTP header values must not contain CR, LF or NUL.');
            }
        }
        return $normalized;
    }
}
