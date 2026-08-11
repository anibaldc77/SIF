<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

use InvalidArgumentException;

final readonly class CredentialStatusProfile
{
    /**
     * @param list<CredentialStatusPurpose> $supportedPurposes
     * @param array<string, mixed> $options
     */
    public function __construct(
        private CredentialStatusMechanism $mechanism,
        private string $profileVersion,
        private array $supportedPurposes,
        private array $options = []
    ) {
        if (
            trim($this->profileVersion) === ''
            || $this->supportedPurposes === []
        ) {
            throw new InvalidArgumentException(
                'Credential status profile is invalid.'
            );
        }
    }

    public function mechanism(): CredentialStatusMechanism
    {
        return $this->mechanism;
    }

    public function profileVersion(): string
    {
        return $this->profileVersion;
    }

    /**
     * @return list<CredentialStatusPurpose>
     */
    public function supportedPurposes(): array
    {
        return $this->supportedPurposes;
    }

    /**
     * @return array<string, mixed>
     */
    public function options(): array
    {
        return $this->options;
    }
}
