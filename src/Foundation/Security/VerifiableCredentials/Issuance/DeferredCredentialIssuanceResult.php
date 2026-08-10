<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

final readonly class DeferredCredentialIssuanceResult
{
    public function __construct(
        private bool $ready,
        private ?CredentialIssuanceResponse $response = null,
        private ?string $reason = null
    ) {
    }

    public function ready(): bool
    {
        return $this->ready;
    }

    public function response(): ?CredentialIssuanceResponse
    {
        return $this->response;
    }

    public function reason(): ?string
    {
        return $this->reason;
    }
}
