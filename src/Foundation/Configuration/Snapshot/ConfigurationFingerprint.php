<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Snapshot;

use InvalidArgumentException;

final readonly class ConfigurationFingerprint
{
    public const ALGORITHM = 'sha256';

    public function __construct(
        private string $digest,
        private string $algorithm = self::ALGORITHM,
    ) {
        if ($algorithm !== self::ALGORITHM) {
            throw new InvalidArgumentException('Unsupported configuration fingerprint algorithm: ' . $algorithm);
        }

        if (preg_match('/^[a-f0-9]{64}$/', $digest) !== 1) {
            throw new InvalidArgumentException('Configuration fingerprint digest must be a lowercase SHA-256 hexadecimal value.');
        }
    }

    public static function fromCanonicalPayload(string $payload): self
    {
        return new self(hash(self::ALGORITHM, $payload));
    }

    public function algorithm(): string
    {
        return $this->algorithm;
    }

    public function digest(): string
    {
        return $this->digest;
    }

    public function value(): string
    {
        return $this->algorithm . ':' . $this->digest;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value(), $other->value());
    }
}
