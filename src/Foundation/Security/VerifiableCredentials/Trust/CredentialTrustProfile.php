<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust;

use InvalidArgumentException;

final readonly class CredentialTrustProfile
{
    /**
     * @param list<CredentialTrustModel> $allowedModels
     * @param list<CredentialTrustEntityRole> $requiredRoles
     * @param array<string, mixed> $options
     */
    public function __construct(
        private string $name,
        private string $profileVersion,
        private array $allowedModels,
        private array $requiredRoles = [],
        private array $options = []
    ) {
        if (
            trim($this->name) === ''
            || trim($this->profileVersion) === ''
            || $this->allowedModels === []
        ) {
            throw new InvalidArgumentException(
                'Credential trust profile is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function profileVersion(): string
    {
        return $this->profileVersion;
    }

    /** @return list<CredentialTrustModel> */
    public function allowedModels(): array
    {
        return $this->allowedModels;
    }

    /** @return list<CredentialTrustEntityRole> */
    public function requiredRoles(): array
    {
        return $this->requiredRoles;
    }

    /** @return array<string, mixed> */
    public function options(): array
    {
        return $this->options;
    }
}
