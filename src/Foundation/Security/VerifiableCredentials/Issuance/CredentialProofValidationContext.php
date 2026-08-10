<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialProofValidationContext
{
    public function __construct(
        private string $credentialIssuer,
        private string $clientId,
        private string $subjectId,
        private CredentialNonce $nonce,
        private DateTimeImmutable $evaluatedAt
    ) {
        if (
            trim($this->credentialIssuer) === ''
            || trim($this->clientId) === ''
            || trim($this->subjectId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential proof validation context is invalid.'
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

    public function nonce(): CredentialNonce
    {
        return $this->nonce;
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }
}
