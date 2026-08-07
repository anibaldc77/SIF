<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\RecoveryCode;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryCodeException;

final readonly class RecoveryCode
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtoupper(trim($value));

        if (
            strlen($normalized) < 12
            || strlen($normalized) > 64
            || preg_match('/^[A-Z0-9]+(?:-[A-Z0-9]+)*$/', $normalized) !== 1
        ) {
            throw new InvalidRecoveryCodeException(
                'Recovery code must be bounded and use grouped URL-safe characters.'
            );
        }

        $this->value = $normalized;
    }

    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function digest(): RecoveryCodeDigest
    {
        return new RecoveryCodeDigest(hash('sha256', $this->value));
    }

    public function __toString(): string
    {
        return '[REDACTED]';
    }

    /** @return array{value:string} */
    public function __debugInfo(): array
    {
        return ['value' => '[REDACTED]'];
    }

    public function __clone()
    {
        throw new \LogicException('Recovery codes cannot be cloned.');
    }

    public function __serialize(): array
    {
        throw new \LogicException('Recovery codes cannot be serialized.');
    }
}
