<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiDeploymentProfile
{
    /**
     * @param list<string> $requiredCapabilities
     * @param list<string> $requiredSecurityControls
     */
    public function __construct(
        private string $name,
        private string $version,
        private array $requiredCapabilities = [],
        private array $requiredSecurityControls = []
    ) {
        if (trim($this->name) === '' || trim($this->version) === '') {
            throw new InvalidArgumentException(
                'FAPI deployment profile name and version are required.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->version;
    }

    /** @return list<string> */
    public function requiredCapabilities(): array
    {
        return $this->requiredCapabilities;
    }

    /** @return list<string> */
    public function requiredSecurityControls(): array
    {
        return $this->requiredSecurityControls;
    }
}
