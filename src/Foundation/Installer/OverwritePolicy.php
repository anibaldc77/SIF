<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidOverwritePolicyException;

final readonly class OverwritePolicy
{
    private const DENY = 'deny';
    private const IF_UNCHANGED = 'if-unchanged';
    private const ALLOW = 'allow';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (!in_array($value, [self::DENY, self::IF_UNCHANGED, self::ALLOW], true)) {
            throw new InvalidOverwritePolicyException(sprintf('Invalid overwrite policy "%s".', $value));
        }

        $this->value = $value;
    }

    public static function deny(): self { return new self(self::DENY); }
    public static function ifUnchanged(): self { return new self(self::IF_UNCHANGED); }
    public static function allow(): self { return new self(self::ALLOW); }
    public function permitsOverwrite(): bool { return $this->value !== self::DENY; }
    public function requiresExpectedFingerprint(): bool { return $this->value === self::IF_UNCHANGED; }
    public function value(): string { return $this->value; }
    public function equals(self $other): bool { return $this->value === $other->value; }
    public function __toString(): string { return $this->value; }
}
