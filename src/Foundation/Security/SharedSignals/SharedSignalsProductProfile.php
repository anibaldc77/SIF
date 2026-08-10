<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

use InvalidArgumentException;

final readonly class SharedSignalsProductProfile
{
    public function __construct(
        private string $name,
        private SharedSignalsProductCapabilities $capabilities,
        private bool $requireReplayProtection = true,
        private bool $requireOperationalReadiness = true,
        private bool $requireContinuousAccessReaction = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Shared Signals product profile name is invalid.'
            );
        }
    }

    public function name(): string { return $this->name; }
    public function capabilities(): SharedSignalsProductCapabilities { return $this->capabilities; }
    public function requireReplayProtection(): bool { return $this->requireReplayProtection; }
    public function requireOperationalReadiness(): bool { return $this->requireOperationalReadiness; }
    public function requireContinuousAccessReaction(): bool { return $this->requireContinuousAccessReaction; }
}
