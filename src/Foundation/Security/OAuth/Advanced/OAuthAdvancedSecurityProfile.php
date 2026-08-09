<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

use InvalidArgumentException;

final readonly class OAuthAdvancedSecurityProfile
{
    /**
     * @param list<OAuthAdvancedSecurityCapability> $capabilities
     */
    public function __construct(
        private string $name,
        private array $capabilities
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'OAuth advanced security profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<OAuthAdvancedSecurityCapability>
     */
    public function capabilities(): array
    {
        return $this->capabilities;
    }

    public function supports(
        OAuthAdvancedSecurityCapability $capability
    ): bool {
        foreach ($this->capabilities as $supported) {
            if ($supported->value() === $capability->value()) {
                return true;
            }
        }

        return false;
    }
}
