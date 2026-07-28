<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Enablement;

use InvalidArgumentException;

final readonly class ModuleEnablementDecision
{
    private function __construct(
        private bool $enabled,
        private ?string $reasonCode,
    ) {
        if (!$enabled && ($reasonCode === null || trim($reasonCode) === '')) {
            throw new InvalidArgumentException('A disabled module requires a non-empty safe reason code.');
        }
        if ($enabled && $reasonCode !== null) {
            throw new InvalidArgumentException('An enabled module cannot have a disablement reason code.');
        }
    }

    public static function enabled(): self
    {
        return new self(true, null);
    }

    public static function disabled(string $reasonCode): self
    {
        return new self(false, $reasonCode);
    }

    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    public function reasonCode(): ?string
    {
        return $this->reasonCode;
    }
}
