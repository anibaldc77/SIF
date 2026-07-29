<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidMutationClassificationException;

final readonly class MutationClassification
{
    private const NONE = 'none';
    private const FILESYSTEM = 'filesystem';
    private const CONFIGURATION = 'configuration';
    private const SECRET_REFERENCE = 'secret-reference';
    private const INFRASTRUCTURE = 'infrastructure';
    private const MIGRATION = 'migration';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (
            $value === ''
            || strlen($value) > 64
            || preg_match('/^[a-z][a-z0-9-]*$/D', $value) !== 1
        ) {
            throw new InvalidMutationClassificationException(
                sprintf('Invalid mutation classification "%s".', $value),
            );
        }

        $this->value = $value;
    }

    public static function none(): self
    {
        return new self(self::NONE);
    }

    public static function filesystem(): self
    {
        return new self(self::FILESYSTEM);
    }

    public static function configuration(): self
    {
        return new self(self::CONFIGURATION);
    }

    public static function secretReference(): self
    {
        return new self(self::SECRET_REFERENCE);
    }

    public static function infrastructure(): self
    {
        return new self(self::INFRASTRUCTURE);
    }

    public static function migration(): self
    {
        return new self(self::MIGRATION);
    }

    public function mutatesState(): bool
    {
        return $this->value !== self::NONE;
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
