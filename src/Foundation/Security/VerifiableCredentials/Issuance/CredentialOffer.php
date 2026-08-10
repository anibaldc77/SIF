<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialOffer
{
    /**
     * @param list<string> $credentialConfigurationIds
     * @param list<string> $grantTypes
     */
    public function __construct(
        private string $credentialIssuer,
        private array $credentialConfigurationIds,
        private array $grantTypes = [],
        private ?string $issuerState = null
    ) {
        if (
            trim($this->credentialIssuer) === ''
            || $this->credentialConfigurationIds === []
        ) {
            throw new InvalidArgumentException(
                'Credential offer is invalid.'
            );
        }
    }

    public function credentialIssuer(): string
    {
        return $this->credentialIssuer;
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
    public function grantTypes(): array
    {
        return $this->grantTypes;
    }

    public function issuerState(): ?string
    {
        return $this->issuerState;
    }
}
