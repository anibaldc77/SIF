<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialAuthorizationDetail
{
    /**
     * @param list<string> $credentialConfigurationIds
     * @param list<string> $locations
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $type,
        private array $credentialConfigurationIds,
        private array $locations = [],
        private array $metadata = []
    ) {
        if (
            trim($this->type) === ''
            || $this->credentialConfigurationIds === []
        ) {
            throw new InvalidArgumentException(
                'Credential authorization detail is invalid.'
            );
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    /**
     * @return list<string>
     */
    public function credentialConfigurationIds(): array
    {
        return $this->credentialConfigurationIds;
    }

    /**
     * @return list<string>
     */
    public function locations(): array
    {
        return $this->locations;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
