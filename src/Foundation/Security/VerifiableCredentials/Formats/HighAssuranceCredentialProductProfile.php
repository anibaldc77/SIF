<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

use InvalidArgumentException;

final readonly class HighAssuranceCredentialProductProfile
{
    public function __construct(
        private string $name,
        private HighAssuranceCredentialProductCapabilities $capabilities,
        private bool $requireIssuerTrust = true,
        private bool $requireStatusValidation = true,
        private bool $requireHolderOrDeviceBinding = true,
        private bool $requirePrivacyPolicy = true,
        private bool $requireOperationalReadiness = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'High assurance credential product profile is invalid.'
            );
        }
    }

    public function name(): string { return $this->name; }
    public function capabilities(): HighAssuranceCredentialProductCapabilities { return $this->capabilities; }
    public function requireIssuerTrust(): bool { return $this->requireIssuerTrust; }
    public function requireStatusValidation(): bool { return $this->requireStatusValidation; }
    public function requireHolderOrDeviceBinding(): bool { return $this->requireHolderOrDeviceBinding; }
    public function requirePrivacyPolicy(): bool { return $this->requirePrivacyPolicy; }
    public function requireOperationalReadiness(): bool { return $this->requireOperationalReadiness; }
}
