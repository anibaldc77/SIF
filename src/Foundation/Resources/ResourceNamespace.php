<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourceNamespaceException;

final readonly class ResourceNamespace
{
    public const GLOBAL = 'global';

    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || strlen($value) > 128 || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $value) !== 1) {
            throw new InvalidResourceNamespaceException(sprintf('Invalid resource namespace "%s".', $value));
        }

        $this->value = $value;
    }

    public static function global(): self
    {
        return new self(self::GLOBAL);
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
