<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryTokenException;

final readonly class RecoveryTokenDigest
{
    private const ALGORITHM = 'sha256';

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (preg_match('/^[a-f0-9]{64}$/', $normalized) !== 1) {
            throw new InvalidRecoveryTokenException('Recovery token digest must be a SHA-256 hexadecimal digest.');
        }

        $this->value = $normalized;
    }

    public static function fromToken(RecoveryToken $token): self
    {
        return new self($token->expose(static fn (string $value): string => hash(self::ALGORITHM, $value)));
    }

    public function value(): string
    {
        return $this->value;
    }

    public function matches(RecoveryToken $token): bool
    {
        $candidate = self::fromToken($token);

        return hash_equals($this->value, $candidate->value);
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }

    public function algorithm(): string
    {
        return self::ALGORITHM;
    }
}
