<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Exceptions\InvalidConfigurationKeyException;

final readonly class ConfigurationKey
{
    private string $value;

    /** @var non-empty-list<non-empty-string> */
    private array $segments;

    public function __construct(string $value)
    {
        $normalized = trim($value);

        if ($normalized === '') {
            throw InvalidConfigurationKeyException::empty();
        }

        $segments = explode('.', $normalized);

        foreach ($segments as $segment) {
            if ($segment === '') {
                throw InvalidConfigurationKeyException::emptySegment($normalized);
            }
        }

        /** @var non-empty-list<non-empty-string> $segments */
        $this->segments = $segments;
        $this->value = $normalized;
    }

    public function value(): string
    {
        return $this->value;
    }

    /** @return non-empty-list<non-empty-string> */
    public function segments(): array
    {
        return $this->segments;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
