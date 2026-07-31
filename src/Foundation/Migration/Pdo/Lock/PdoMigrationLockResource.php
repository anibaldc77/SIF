<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Lock;

use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationLockResourceException;

final readonly class PdoMigrationLockResource
{
    private string $value;

    public function __construct(string $value = 'sif:migrations')
    {
        $value = trim($value);

        if ($value === '' || strlen($value) > 128) {
            throw new InvalidPdoMigrationLockResourceException(
                'PDO migration lock resource must contain between 1 and 128 bytes.',
            );
        }

        if (preg_match('/^[A-Za-z0-9][A-Za-z0-9_.:\/-]*$/D', $value) !== 1) {
            throw new InvalidPdoMigrationLockResourceException(
                'PDO migration lock resource contains unsupported characters.',
            );
        }

        $this->value = $value;
    }

    public function value(): string
    {
        return $this->value;
    }

    /** @return array{0: int, 1: int} */
    public function postgresqlKeys(): array
    {
        $digest = hash('sha256', $this->value, true);
        $parts = unpack('Nfirst/Nsecond', substr($digest, 0, 8));

        if (!is_array($parts)) {
            throw new InvalidPdoMigrationLockResourceException('Unable to derive PostgreSQL advisory lock keys.');
        }

        return [self::signedInt32((int) $parts['first']), self::signedInt32((int) $parts['second'])];
    }

    private static function signedInt32(int $value): int
    {
        return $value > 0x7fffffff ? $value - 0x100000000 : $value;
    }
}
