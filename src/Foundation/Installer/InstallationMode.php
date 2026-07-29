<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidInstallationModeException;

final readonly class InstallationMode
{
    private const FRESH = 'fresh';
    private const REPAIR = 'repair';
    private const UPGRADE = 'upgrade';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (
            $value === ''
            || strlen($value) > 64
            || preg_match('/^[a-z][a-z0-9-]*$/D', $value) !== 1
        ) {
            throw new InvalidInstallationModeException(
                sprintf('Invalid installation mode "%s".', $value),
            );
        }

        $this->value = $value;
    }

    public static function fresh(): self
    {
        return new self(self::FRESH);
    }

    public static function repair(): self
    {
        return new self(self::REPAIR);
    }

    public static function upgrade(): self
    {
        return new self(self::UPGRADE);
    }

    public function isFresh(): bool
    {
        return $this->value === self::FRESH;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
