<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OpenId4VcHaipWalletAttestation
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        private string $issuer,
        private string $subject,
        private string $audience,
        private DateTimeImmutable $issuedAt,
        private DateTimeImmutable $expiresAt,
        private array $claims = []
    ) {
        if (
            trim($this->issuer) === ''
            || trim($this->subject) === ''
            || trim($this->audience) === ''
            || $this->expiresAt <= $this->issuedAt
        ) {
            throw new InvalidArgumentException(
                'OpenID4VC HAIP Wallet Attestation is invalid.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @return array<string, mixed> */
    public function claims(): array
    {
        return $this->claims;
    }
}
