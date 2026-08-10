<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSecurityProfile
{
    /**
     * @param list<FapiSecurityCapability> $requiredCapabilities
     */
    public function __construct(
        private string $name,
        private array $requiredCapabilities
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'FAPI security profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return list<FapiSecurityCapability>
     */
    public function requiredCapabilities(): array
    {
        return $this->requiredCapabilities;
    }

    public function requires(FapiSecurityCapability $capability): bool
    {
        foreach ($this->requiredCapabilities as $required) {
            if ($required->value() === $capability->value()) {
                return true;
            }
        }

        return false;
    }
}
