<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Authentication;

use Sif\Foundation\Security\Exceptions\InvalidPasswordHashProviderResultException;
use Sif\Foundation\Security\Password\StoredPasswordHash;

final readonly class PasswordHashProviderResult
{
    private function __construct(private ?StoredPasswordHash $hash)
    {
    }

    public static function found(StoredPasswordHash $hash): self
    {
        return new self($hash);
    }

    public static function notFound(): self
    {
        return new self(null);
    }

    public function wasFound(): bool
    {
        return $this->hash !== null;
    }

    public function hash(): StoredPasswordHash
    {
        if ($this->hash === null) {
            throw new InvalidPasswordHashProviderResultException(
                'Password hash provider result does not contain a hash.'
            );
        }

        return $this->hash;
    }
}
