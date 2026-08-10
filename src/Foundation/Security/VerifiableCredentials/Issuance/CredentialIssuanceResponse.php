<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class CredentialIssuanceResponse
{
    public function __construct(
        private ?string $credential = null,
        private ?string $transactionId = null,
        private ?string $cNonce = null
    ) {
        if ($this->credential === null && $this->transactionId === null) {
            throw new InvalidArgumentException(
                'Credential issuance response requires credential or transaction id.'
            );
        }
    }

    public function credential(): ?string
    {
        return $this->credential;
    }

    public function transactionId(): ?string
    {
        return $this->transactionId;
    }

    public function cNonce(): ?string
    {
        return $this->cNonce;
    }

    public function deferred(): bool
    {
        return $this->credential === null && $this->transactionId !== null;
    }
}
