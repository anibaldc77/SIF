<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustMarks;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OpenIdFederationTrustMark
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        private string $trustMarkId,
        private string $issuerEntityId,
        private string $subjectEntityId,
        private DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $expiresAt = null,
        private array $claims = []
    ) {
        if (
            trim($this->trustMarkId) === ''
            || trim($this->issuerEntityId) === ''
            || trim($this->subjectEntityId) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Trust Mark is invalid.'
            );
        }

        if (
            $this->expiresAt !== null
            && $this->expiresAt <= $this->issuedAt
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Trust Mark validity interval is invalid.'
            );
        }
    }

    public function trustMarkId(): string
    {
        return $this->trustMarkId;
    }

    public function issuerEntityId(): string
    {
        return $this->issuerEntityId;
    }

    public function subjectEntityId(): string
    {
        return $this->subjectEntityId;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /** @return array<string, mixed> */
    public function claims(): array
    {
        return $this->claims;
    }
}
