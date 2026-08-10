<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiProductProfile
{
    public function __construct(
        private string $name,
        private FapiProductCapabilities $capabilities,
        private bool $requireStrictConformance = true,
        private bool $requireDeploymentReadiness = true,
        private bool $requireMessageSigning = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'FAPI product profile name is invalid.'
            );
        }
    }

    public function name(): string { return $this->name; }
    public function capabilities(): FapiProductCapabilities { return $this->capabilities; }
    public function requireStrictConformance(): bool { return $this->requireStrictConformance; }
    public function requireDeploymentReadiness(): bool { return $this->requireDeploymentReadiness; }
    public function requireMessageSigning(): bool { return $this->requireMessageSigning; }
}
