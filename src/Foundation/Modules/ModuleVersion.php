<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Exceptions\InvalidModuleVersionException;

final readonly class ModuleVersion
{
    private const PATTERN = '/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)(?:-([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?(?:\+([0-9A-Za-z-]+(?:\.[0-9A-Za-z-]+)*))?$/D';

    private int $major;
    private int $minor;
    private int $patch;
    private ?string $preRelease;
    private ?string $build;

    public function __construct(private string $value)
    {
        if (preg_match(self::PATTERN, $value, $matches) !== 1) {
            throw InvalidModuleVersionException::forValue($value);
        }
        $this->major = (int) $matches[1];
        $this->minor = (int) $matches[2];
        $this->patch = (int) $matches[3];
        $this->preRelease = isset($matches[4]) && $matches[4] !== '' ? $matches[4] : null;
        $this->build = isset($matches[5]) && $matches[5] !== '' ? $matches[5] : null;
        if ($this->hasInvalidNumericPreReleaseIdentifier()) {
            throw InvalidModuleVersionException::forValue($value);
        }
    }

    public function value(): string { return $this->value; }
    public function major(): int { return $this->major; }
    public function minor(): int { return $this->minor; }
    public function patch(): int { return $this->patch; }
    public function preRelease(): ?string { return $this->preRelease; }
    public function build(): ?string { return $this->build; }
    public function equals(self $other): bool { return $this->value === $other->value; }

    public function compareTo(self $other): int
    {
        $core = [$this->major, $this->minor, $this->patch] <=> [$other->major, $other->minor, $other->patch];
        if ($core !== 0) { return $core; }
        if ($this->preRelease === $other->preRelease) { return 0; }
        if ($this->preRelease === null) { return 1; }
        if ($other->preRelease === null) { return -1; }
        $left = explode('.', $this->preRelease);
        $right = explode('.', $other->preRelease);
        $length = max(count($left), count($right));
        for ($index = 0; $index < $length; $index++) {
            if (!isset($left[$index])) { return -1; }
            if (!isset($right[$index])) { return 1; }
            $comparison = $this->compareIdentifier($left[$index], $right[$index]);
            if ($comparison !== 0) { return $comparison; }
        }
        return 0;
    }

    public function __toString(): string { return $this->value; }

    private function hasInvalidNumericPreReleaseIdentifier(): bool
    {
        if ($this->preRelease === null) { return false; }
        foreach (explode('.', $this->preRelease) as $identifier) {
            if (ctype_digit($identifier) && strlen($identifier) > 1 && $identifier[0] === '0') { return true; }
        }
        return false;
    }

    private function compareIdentifier(string $left, string $right): int
    {
        $leftNumeric = ctype_digit($left);
        $rightNumeric = ctype_digit($right);
        if ($leftNumeric && $rightNumeric) { return (int) $left <=> (int) $right; }
        if ($leftNumeric) { return -1; }
        if ($rightNumeric) { return 1; }
        return $left <=> $right;
    }
}
