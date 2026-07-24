<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\Contracts\MutableEnvironmentInterface;
use Sif\Foundation\Environment\Exceptions\FrozenEnvironmentException;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentKeyException;

final class EnvironmentRepository implements MutableEnvironmentInterface
{
    /** @var array<string, string> */
    private array $values;

    private bool $frozen = false;

    /** @param array<string, string>|EnvironmentProviderInterface $source */
    public function __construct(EnvironmentProviderInterface|array $source = [])
    {
        $this->values = $source instanceof EnvironmentProviderInterface
            ? $source->all()
            : $source;

        foreach ($this->values as $key => $value) {
            $this->assertKey($key);
            if (!is_string($value)) {
                throw new \InvalidArgumentException('Environment repository values must be strings.');
            }
        }
    }

    public function has(string $key): bool
    {
        $this->assertKey($key);

        return array_key_exists($key, $this->values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $this->assertKey($key);

        return $this->values[$key] ?? $default;
    }

    /** @return array<string, string> */
    public function all(): array
    {
        return $this->values;
    }

    public function set(string $key, string $value): void
    {
        $this->assertMutable();
        $this->assertKey($key);
        $this->values[$key] = $value;
    }

    /** @param array<string, string> $values */
    public function replace(array $values): void
    {
        $this->assertMutable();
        foreach ($values as $key => $value) {
            $this->assertKey($key);
            if (!is_string($value)) {
                throw new \InvalidArgumentException('Environment repository values must be strings.');
            }
        }
        $this->values = $values;
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    private function assertMutable(): void
    {
        if ($this->frozen) {
            throw FrozenEnvironmentException::mutationAttempted();
        }
    }

    private function assertKey(string $key): void
    {
        if (trim($key) === '') {
            throw InvalidEnvironmentKeyException::empty();
        }
    }
}
