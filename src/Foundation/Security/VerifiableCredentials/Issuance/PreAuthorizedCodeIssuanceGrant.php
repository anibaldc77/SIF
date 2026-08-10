<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use InvalidArgumentException;

final readonly class PreAuthorizedCodeIssuanceGrant
{
    public function __construct(
        private string $preAuthorizedCode,
        private ?string $txCode = null
    ) {
        if (trim($this->preAuthorizedCode) === '') {
            throw new InvalidArgumentException(
                'Pre-Authorized Code issuance grant is invalid.'
            );
        }
    }

    public function preAuthorizedCode(): string
    {
        return $this->preAuthorizedCode;
    }

    public function txCode(): ?string
    {
        return $this->txCode;
    }
}
