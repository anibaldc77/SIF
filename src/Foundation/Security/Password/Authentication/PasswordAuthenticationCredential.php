<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Authentication;

use Sif\Foundation\Security\Contracts\CredentialInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\Password\PasswordCredential;

final readonly class PasswordAuthenticationCredential implements CredentialInterface
{
    public function __construct(
        private IdentityLookupKey $lookupKey,
        private PasswordCredential $password
    ) {
    }

    public function type(): CredentialType
    {
        return new CredentialType('password');
    }

    public function lookupKey(): IdentityLookupKey
    {
        return $this->lookupKey;
    }

    public function password(): PasswordCredential
    {
        return $this->password;
    }

    /** @return array{lookup_key: string, password: string} */
    public function __debugInfo(): array
    {
        return [
            'lookup_key' => $this->lookupKey->value(),
            'password' => '[REDACTED]',
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException('Password authentication credentials cannot be serialized.');
    }
}
