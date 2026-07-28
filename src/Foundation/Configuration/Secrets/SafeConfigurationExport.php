<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Secrets;

final readonly class SafeConfigurationExport
{
    /**
     * @param array<array-key, mixed> $values
     * @param list<string> $redactedKeys
     */
    public function __construct(
        private array $values,
        private array $redactedKeys,
    ) {
    }

    /** @return array<array-key, mixed> */
    public function values(): array
    {
        return $this->values;
    }

    /** @return list<string> */
    public function redactedKeys(): array
    {
        return $this->redactedKeys;
    }

    public function containsRedactions(): bool
    {
        return $this->redactedKeys !== [];
    }
}
