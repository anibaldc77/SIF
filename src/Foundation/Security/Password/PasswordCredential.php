<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password;

use Sif\Foundation\Security\Contracts\CredentialInterface;
use Sif\Foundation\Security\Credentials\CredentialType;

final readonly class PasswordCredential implements CredentialInterface
{
    public function __construct(private PasswordSecret $secret)
    {
    }

    public function type(): CredentialType
    {
        return new CredentialType('password');
    }

    public function secret(): PasswordSecret
    {
        return $this->secret;
    }

    /** @return array<string, string> */
    public function __debugInfo(): array
    {
        return ['secret' => '[REDACTED]'];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException('Password credentials cannot be serialized.');
    }
}
