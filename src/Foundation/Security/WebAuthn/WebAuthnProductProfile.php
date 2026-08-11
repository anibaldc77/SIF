<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnProductProfile
{
    public function __construct(
        private string $name,
        private WebAuthnProductCapabilities $capabilities,
        private bool $requireUserVerification = true,
        private bool $requireChallengeReplayProtection = true,
        private bool $requireOriginValidation = true,
        private bool $requireRpIdValidation = true,
        private bool $requireOperationalReadiness = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'WebAuthn product profile name is invalid.'
            );
        }
    }

    public function name(): string { return $this->name; }
    public function capabilities(): WebAuthnProductCapabilities { return $this->capabilities; }
    public function requireUserVerification(): bool { return $this->requireUserVerification; }
    public function requireChallengeReplayProtection(): bool { return $this->requireChallengeReplayProtection; }
    public function requireOriginValidation(): bool { return $this->requireOriginValidation; }
    public function requireRpIdValidation(): bool { return $this->requireRpIdValidation; }
    public function requireOperationalReadiness(): bool { return $this->requireOperationalReadiness; }
}
