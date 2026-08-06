<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\IdentityProvider;

use Sif\Foundation\Security\Exceptions\InvalidIdentityProviderResultException;

final readonly class IdentityProviderResult
{
    private function __construct(private ?IdentityProviderRecord $record)
    {
    }

    public static function found(IdentityProviderRecord $record): self
    {
        return new self($record);
    }

    public static function notFound(): self
    {
        return new self(null);
    }

    public function wasFound(): bool
    {
        return $this->record !== null;
    }

    public function record(): IdentityProviderRecord
    {
        if ($this->record === null) {
            throw new InvalidIdentityProviderResultException(
                'Identity provider result does not contain a record.'
            );
        }

        return $this->record;
    }
}
