<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialIssuanceTransaction
{
    public function __construct(
        private string $transactionId,
        private string $clientId,
        private string $subjectId,
        private string $credentialConfigurationId,
        private DateTimeImmutable $createdAt
    ) {
        if (
            trim($this->transactionId) === ''
            || trim($this->clientId) === ''
            || trim($this->subjectId) === ''
            || trim($this->credentialConfigurationId) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential issuance transaction is invalid.'
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

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }
}
