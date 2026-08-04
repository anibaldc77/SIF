<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Flash;

final class FlashBag
{
    /** @var array<string, mixed> */
    private array $available;
    /** @var array<string, mixed> */
    private array $new;
    /** @var array<string, true> */
    private array $kept = [];

    /**
     * @param array<string, mixed> $available
     * @param array<string, mixed> $new
     */
    public function __construct(array $available = [], array $new = [])
    {
        $this->available = $available;
        $this->new = $new;
    }

    public function put(string $key, mixed $value): void
    {
        $this->new[$key] = $value;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->available);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->available[$key] ?? $default;
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return $this->available;
    }

    public function keep(string $key): void
    {
        if ($this->has($key)) {
            $this->kept[$key] = true;
        }
    }

    public function reflash(): void
    {
        foreach (array_keys($this->available) as $key) {
            $this->kept[$key] = true;
        }
    }

    /** @return array<string, mixed> */
    public function nextRequestData(): array
    {
        return (new FlashTransition($this->available, $this->new, $this->kept))->nextRequestData();
    }
}
