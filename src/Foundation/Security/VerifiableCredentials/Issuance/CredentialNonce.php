<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Issuance;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialNonce
{
    public function __construct(
        private string $value,
        private DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $expiresAt = null
    ) {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'Credential nonce is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }
}
