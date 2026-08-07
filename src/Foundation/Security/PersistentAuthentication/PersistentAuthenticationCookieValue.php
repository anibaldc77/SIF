<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use Sif\Foundation\Security\Exceptions\InvalidPersistentAuthenticationCredentialException;

final readonly class PersistentAuthenticationCookieValue
{
    private const SEPARATOR = '.';

    public function __construct(
        private PersistentAuthenticationToken $token
    ) {
    }

    public function expose(callable $consumer): mixed
    {
        return $this->token->validator()->expose(
            fn (string $validator): mixed => $consumer(
                $this->token->selector()->value()
                . self::SEPARATOR
                . $validator
            )
        );
    }

    public static function parse(string $value): PersistentAuthenticationToken
    {
        $parts = explode(self::SEPARATOR, trim($value), 2);

        if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
            throw new InvalidPersistentAuthenticationCredentialException(
                'Persistent authentication cookie value is invalid.'
            );
        }

        return new PersistentAuthenticationToken(
            new PersistentAuthenticationSelector($parts[0]),
            new PersistentAuthenticationValidator($parts[1])
        );
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
            'Persistent authentication cookie values cannot be serialized.'
        );
    }
}
