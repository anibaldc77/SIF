<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class ValidatedAccessToken
{
    /**
     * @param array<string, scalar|null> $attributes
     */
    public function __construct(
        private IdentityId $subject,
        private ScopeSet $scopes,
        private DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $issuedAt = null,
        private array $attributes = []
    ) {
        if (
            $this->issuedAt !== null
            && $this->expiresAt <= $this->issuedAt
        ) {
            throw new InvalidArgumentException(
                'Validated access token expiry must be after issuance.'
            );
        }

        foreach ($attributes as $name => $_value) {
            if (
                $name === ''
                || strlen($name) > 160
                || preg_match('/^[A-Za-z0-9._:-]+$/', $name) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Validated access token attribute name is invalid.'
                );
            }
        }
    }

    public function subject(): IdentityId
    {
        return $this->subject;
    }

    public function scopes(): ScopeSet
    {
        return $this->scopes;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    public function isActiveAt(DateTimeImmutable $now): bool
    {
        return $now < $this->expiresAt;
    }

    /** @return array<string, scalar|null> */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
