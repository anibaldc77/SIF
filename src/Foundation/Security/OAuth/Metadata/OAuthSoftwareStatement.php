<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthSoftwareStatement
{
    /**
     * @param array<string, mixed> $claims
     */
    public function __construct(
        private string $serialized,
        private string $issuer,
        private string $subject,
        private DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $expiresAt,
        private array $claims = []
    ) {
        if (
            trim($this->serialized) === ''
            || trim($this->issuer) === ''
            || trim($this->subject) === ''
            || (
                $this->expiresAt !== null
                && $this->expiresAt <= $this->issuedAt
            )
        ) {
            throw new InvalidArgumentException(
                'OAuth software statement is invalid.'
            );
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function issuedAt(): DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function claims(): array
    {
        return $this->claims;
    }
}
