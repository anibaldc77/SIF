<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;
use InvalidArgumentException;
final readonly class CredentialIssuanceProductProfile
{
    public function __construct(
        private string $name,
        private CredentialIssuanceProductCapabilities $capabilities,
        private bool $requireProofOfPossession = true,
        private bool $requireReplayProtection = true,
        private bool $requireTransactionBinding = true,
        private bool $requireOperationalReadiness = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Credential issuance product profile name is invalid.');
        }
    }
    public function name(): string { return $this->name; }
    public function capabilities(): CredentialIssuanceProductCapabilities { return $this->capabilities; }
    public function requireProofOfPossession(): bool { return $this->requireProofOfPossession; }
    public function requireReplayProtection(): bool { return $this->requireReplayProtection; }
    public function requireTransactionBinding(): bool { return $this->requireTransactionBinding; }
    public function requireOperationalReadiness(): bool { return $this->requireOperationalReadiness; }
}
