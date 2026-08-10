<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialAuthorizationContext
{
    public function __construct(
        private string $credentialIssuer,
        private string $clientId,
        private string $subjectId,
        private ?string $authorizationRequestId = null
    ) {
        if (
            trim($this->credentialIssuer) === ''
            || trim($this->clientId) === ''
            || trim($this->subjectId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential authorization context is invalid.'
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

    public function subjectId(): string
    {
        return $this->subjectId;
    }

    public function authorizationRequestId(): ?string
    {
        return $this->authorizationRequestId;
    }
}
