<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceContext
{
    public function __construct(
        private string $issuer,
        private string $subjectId,
        private string $clientId,
        private ?string $accessTokenReference = null
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->subjectId) === ''
            || trim($this->clientId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential issuance context is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subjectId(): string
    {
        return $this->subjectId;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function accessTokenReference(): ?string
    {
        return $this->accessTokenReference;
    }
}
