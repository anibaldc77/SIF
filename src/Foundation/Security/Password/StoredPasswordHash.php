<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password;

use Sif\Foundation\Security\Exceptions\InvalidPasswordHashException;

final readonly class StoredPasswordHash
{
    private const REDACTED = '[REDACTED]';

    /** @var array<string, bool|int|string> */
    private array $parameters;

    /**
     * @param array<string, bool|int|string> $parameters
     */
    public function __construct(
        private PasswordHashAlgorithm $algorithm,
        #[\SensitiveParameter] private string $encodedHash,
        array $parameters = []
    ) {
        if ($encodedHash === '' || strlen($encodedHash) > 8192 || str_contains($encodedHash, "\0")) {
            throw new InvalidPasswordHashException('Stored password hash is invalid.');
        }

        foreach ($parameters as $name => $value) {
            if (preg_match('/^[a-z][a-z0-9._-]{0,63}$/', $name) !== 1) {
                throw new InvalidPasswordHashException('Password hash parameter names must be stable identifiers.');
            }

            if (is_string($value) && (strlen($value) > 256 || preg_match('/[\x00-\x1F\x7F]/', $value) === 1)) {
                throw new InvalidPasswordHashException('Password hash parameter values must be bounded and printable.');
            }
        }

        ksort($parameters, SORT_STRING);
        $this->parameters = $parameters;
    }

    public function algorithm(): PasswordHashAlgorithm
    {
        return $this->algorithm;
    }

    /** @return array<string, bool|int|string> */
    public function parameters(): array
    {
        return $this->parameters;
    }

    /**
     * @template TResult
     * @param callable(string): TResult $consumer
     * @return TResult
     */
    public function exposeEncodedHash(callable $consumer): mixed
    {
        return $consumer($this->encodedHash);
    }

    /** @return array<string, mixed> */
    public function __debugInfo(): array
    {
        return [
            'algorithm' => $this->algorithm->value(),
            'encodedHash' => self::REDACTED,
            'parameters' => $this->parameters,
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new InvalidPasswordHashException('Stored password hashes cannot be serialized implicitly.');
    }
}
