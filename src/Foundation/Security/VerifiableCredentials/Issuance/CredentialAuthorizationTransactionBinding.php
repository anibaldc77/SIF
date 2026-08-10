<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialAuthorizationTransactionBinding
{
    public function __construct(
        private string $transactionId,
        private string $clientId,
        private string $subjectId,
        private string $credentialConfigurationId,
        private ?string $authorizationRequestId = null
    ) {
        if (
            trim($this->transactionId) === ''
            || trim($this->clientId) === ''
            || trim($this->subjectId) === ''
            || trim($this->credentialConfigurationId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential authorization transaction binding is invalid.'
            );
        }
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function subjectId(): string
    {
        return $this->subjectId;
    }

    public function credentialConfigurationId(): string
    {
        return $this->credentialConfigurationId;
    }

    public function authorizationRequestId(): ?string
    {
        return $this->authorizationRequestId;
    }
}
