<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceGrantContext
{
    public function __construct(
        private string $credentialIssuer,
        private string $clientId,
        private ?string $subjectId = null,
        private ?string $issuerState = null
    ) {
        if (
            trim($this->credentialIssuer) === ''
            || trim($this->clientId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential issuance grant context is invalid.'
            );
        }
    }

    public function credentialIssuer(): string
    {
        return $this->credentialIssuer;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function subjectId(): ?string
    {
        return $this->subjectId;
    }

    public function issuerState(): ?string
    {
        return $this->issuerState;
    }
}
