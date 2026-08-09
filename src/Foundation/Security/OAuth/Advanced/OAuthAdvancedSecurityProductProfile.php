<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthAdvancedSecurityProductProfile
{
    public function __construct(
        private string $name,
        private OAuthAdvancedSecurityCapabilities $capabilities,
        private bool $requirePkce = true,
        private bool $requirePar = true,
        private bool $requireSenderConstraint = true
    ) {
        if (trim($name) === '') {
            throw new InvalidArgumentException('OAuth advanced security product profile name is invalid.');
        }
    }

    public function name(): string { return $this->name; }
    public function capabilities(): OAuthAdvancedSecurityCapabilities { return $this->capabilities; }
    public function requirePkce(): bool { return $this->requirePkce; }
    public function requirePar(): bool { return $this->requirePar; }
    public function requireSenderConstraint(): bool { return $this->requireSenderConstraint; }
}
