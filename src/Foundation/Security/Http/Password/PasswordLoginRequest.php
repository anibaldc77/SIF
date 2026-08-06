<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Http\Password;

use JsonException;
use Sif\Foundation\Security\Exceptions\InvalidPasswordLoginRequestException;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordSecret;

final readonly class PasswordLoginRequest
{
    public function __construct(
        private IdentityLookupKey $lookupKey,
        private PasswordCredential $password
    ) {
    }

    public static function fromJson(string $json): self
    {
        try {
            $payload = json_decode($json, true, 8, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidPasswordLoginRequestException('Password login payload must contain valid JSON.', previous: $exception);
        }

        if (!is_array($payload)) {
            throw new InvalidPasswordLoginRequestException('Password login payload must be a JSON object.');
        }

        $lookup = $payload['identity'] ?? null;
        $password = $payload['password'] ?? null;

        if (!is_string($lookup) || !is_string($password)) {
            throw new InvalidPasswordLoginRequestException('Password login payload requires string identity and password fields.');
        }

        try {
            return new self(
                new IdentityLookupKey($lookup),
                new PasswordCredential(new PasswordSecret($password))
            );
        } catch (\Throwable $exception) {
            throw new InvalidPasswordLoginRequestException('Password login payload is invalid.', previous: $exception);
        }
    }

    public function lookupKey(): IdentityLookupKey
    {
        return $this->lookupKey;
    }

    public function password(): PasswordCredential
    {
        return $this->password;
    }

    /** @return array{identity: string, password: string} */
    public function __debugInfo(): array
    {
        return ['identity' => $this->lookupKey->value(), 'password' => '[REDACTED]'];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new \LogicException('Password login requests cannot be serialized.');
    }
}
