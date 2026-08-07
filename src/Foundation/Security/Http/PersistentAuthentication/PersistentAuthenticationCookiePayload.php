<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\PersistentAuthentication;

use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationCookieValue;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationToken;

final readonly class PersistentAuthenticationCookiePayload
{
    public function __construct(
        private PersistentAuthenticationCookieValue $value
    ) {
    }

    public static function fromToken(
        PersistentAuthenticationToken $token
    ): self {
        return new self(
            new PersistentAuthenticationCookieValue($token)
        );
    }

    public static function parse(string $raw): PersistentAuthenticationToken
    {
        return PersistentAuthenticationCookieValue::parse($raw);
    }

    public function expose(callable $consumer): mixed
    {
        return $this->value->expose($consumer);
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

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException(
            'Persistent authentication cookie payloads cannot be serialized.'
        );
    }
}
