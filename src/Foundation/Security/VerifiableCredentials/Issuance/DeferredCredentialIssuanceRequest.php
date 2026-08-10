<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class DeferredCredentialIssuanceRequest
{
    public function __construct(
        private string $transactionId,
        private string $clientId
    ) {
        if (
            trim($this->transactionId) === ''
            || trim($this->clientId) === ''
        ) {
            throw new InvalidArgumentException(
                'Deferred credential issuance request is invalid.'
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
}
