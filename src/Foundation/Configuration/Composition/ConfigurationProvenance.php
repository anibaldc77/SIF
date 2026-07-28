<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Composition;

use Sif\Foundation\Configuration\ConfigurationKey;

final readonly class ConfigurationProvenance
{
    public function __construct(
        private ConfigurationKey $key,
        private string $sourceId,
        private string $sourceType,
        private int $precedence,
        private int $registrationOrder,
        private bool $overrodeEarlierValue,
    ) {
    }

    public function key(): ConfigurationKey
    {
        return $this->key;
    }

    public function sourceId(): string
    {
        return $this->sourceId;
    }

    public function sourceType(): string
    {
        return $this->sourceType;
    }

    public function precedence(): int
    {
        return $this->precedence;
    }

    public function registrationOrder(): int
    {
        return $this->registrationOrder;
    }

    public function overrodeEarlierValue(): bool
    {
        return $this->overrodeEarlierValue;
    }
}
