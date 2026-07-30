<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationChecksumException;

final readonly class MigrationChecksum
{
    private string $algorithm;

    private string $digest;

    public function __construct(string $algorithm, string $digest)
    {
        $algorithm = strtolower(trim($algorithm));
        $digest = strtolower(trim($digest));

        if ($algorithm === '' || preg_match('/^[a-z0-9][a-z0-9-]*$/D', $algorithm) !== 1) {
            throw new InvalidMigrationChecksumException('Migration checksum algorithm is invalid.');
        }

        if ($digest === '' || preg_match('/^[a-f0-9]+$/D', $digest) !== 1 || strlen($digest) % 2 !== 0) {
            throw new InvalidMigrationChecksumException('Migration checksum digest must be non-empty hexadecimal data.');
        }

        $this->algorithm = $algorithm;
        $this->digest = $digest;
    }

    public static function sha256(string $canonicalContent): self
    {
        return new self('sha256', hash('sha256', $canonicalContent));
    }

    public static function parse(string $value): self
    {
        $parts = explode(':', trim($value), 2);

        if (count($parts) !== 2) {
            throw new InvalidMigrationChecksumException(
                'Migration checksum must use the "algorithm:digest" representation.',
            );
        }

        return new self($parts[0], $parts[1]);
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
        return $this->algorithm === $other->algorithm && $this->digest === $other->digest;
    }
}
