<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

use Sif\Foundation\Resources\Exceptions\InvalidResourceOverridePolicyException;

final readonly class ResourceOverridePolicy
{
    public const FORBID = 'forbid';
    public const REPLACE_IF_HIGHER_PRIORITY = 'replace_if_higher_priority';
    public const REPLACE_ALWAYS = 'replace_always';

    public function __construct(private string $value = self::FORBID)
    {
        if (!in_array($value, [self::FORBID, self::REPLACE_IF_HIGHER_PRIORITY, self::REPLACE_ALWAYS], true)) {
            throw new InvalidResourceOverridePolicyException(sprintf('Unknown resource override policy "%s".', $value));
        }
    }

    public static function forbid(): self { return new self(self::FORBID); }
    public static function replaceIfHigherPriority(): self { return new self(self::REPLACE_IF_HIGHER_PRIORITY); }
    public static function replaceAlways(): self { return new self(self::REPLACE_ALWAYS); }
    public function value(): string { return $this->value; }
    public function permitsReplacement(): bool { return $this->value !== self::FORBID; }
    public function requiresHigherPriority(): bool { return $this->value === self::REPLACE_IF_HIGHER_PRIORITY; }
}
