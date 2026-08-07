<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Introspection;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\OAuth2\ScopeSet;

final readonly class TokenIntrospectionResult
{
    /**
     * @param array<string, scalar|null> $attributes
     */
    public function __construct(
        private bool $active,
        private ?string $subject = null,
        private ?ScopeSet $scopes = null,
        private ?DateTimeImmutable $expiresAt = null,
        private ?DateTimeImmutable $issuedAt = null,
        private array $attributes = []
    ) {
        if ($this->active && ($this->subject === null || $this->subject === '')) {
            throw new InvalidArgumentException(
                'Active introspection result requires a subject.'
            );
        }

        foreach ($attributes as $name => $_value) {
            if (
                $name === ''
                || strlen($name) > 160
                || preg_match('/^[A-Za-z0-9._:-]+$/', $name) !== 1
            ) {
                throw new InvalidArgumentException(
                    'Introspection attribute name is invalid.'
                );
            }
        }
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function subject(): ?string
    {
        return $this->subject;
    }

    public function scopes(): ScopeSet
    {
        return $this->scopes ?? new ScopeSet();
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /** @return array<string, scalar|null> */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
