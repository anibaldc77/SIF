<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthClientRegistrationCredential
{
    public function __construct(
        private string $type,
        private string $materialReference,
        private DateTimeImmutable $issuedAt,
        private ?DateTimeImmutable $expiresAt = null
    ) {
        if (
            trim($this->type) === ''
            || trim($this->materialReference) === ''
            || (
                $this->expiresAt !== null
                && $this->expiresAt <= $this->issuedAt
            )
        ) {
            throw new InvalidArgumentException(
                'OAuth client registration credential is invalid.'
            );
        }
    }

    public function type(): string
    {
        return $this->type;
    }

    public function materialReference(): string
    {
        return $this->materialReference;
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
