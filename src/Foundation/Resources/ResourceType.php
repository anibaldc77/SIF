<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourceTypeException;

final readonly class ResourceType
{
    public const STYLESHEET = 'stylesheet';
    public const SCRIPT = 'script';
    public const IMAGE = 'image';
    public const FONT = 'font';
    public const LOCALE = 'locale';
    public const TRANSLATION = 'translation';
    public const GENERIC = 'generic';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if ($value === '' || strlen($value) > 64 || preg_match('/^[a-z][a-z0-9.-]*$/D', $value) !== 1) {
            throw new InvalidResourceTypeException(sprintf('Invalid resource type "%s".', $value));
        }

        $this->value = $value;
    }

    public static function stylesheet(): self { return new self(self::STYLESHEET); }
    public static function script(): self { return new self(self::SCRIPT); }
    public static function image(): self { return new self(self::IMAGE); }
    public static function font(): self { return new self(self::FONT); }
    public static function locale(): self { return new self(self::LOCALE); }
    public static function translation(): self { return new self(self::TRANSLATION); }
    public static function generic(): self { return new self(self::GENERIC); }

    public function value(): string
    {
        return $this->value;
    }

    public function is(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
