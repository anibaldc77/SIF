<?php
declare(strict_types=1);

namespace Sif\Support\ValueObjects;

use Sif\Support\Contracts\StringableInterface;
use Sif\Support\Exceptions\InvalidArgumentException;

final readonly class Uuid implements StringableInterface
{
    private function __construct(private string $value) {}
    public static function v4(): self
    {
        $bytes = random_bytes(16); $bytes[6] = chr((ord($bytes[6]) & 0x0f) | 0x40); $bytes[8] = chr((ord($bytes[8]) & 0x3f) | 0x80);
        return self::fromBytes($bytes);
    }
    public static function fromString(string $value): self
    {
        $value = strtolower($value);
        if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[1-8][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/', $value) !== 1) { throw new InvalidArgumentException('UUID is invalid.'); }
        return new self($value);
    }
    public static function fromBytes(string $bytes): self
    {
        if (strlen($bytes) !== 16) { throw new InvalidArgumentException('UUID binary representation must contain 16 bytes.'); }
        $hex = bin2hex($bytes); return self::fromString(substr($hex, 0, 8).'-'.substr($hex, 8, 4).'-'.substr($hex, 12, 4).'-'.substr($hex, 16, 4).'-'.substr($hex, 20));
    }
    public function toString(): string { return $this->value; }
    public function __toString(): string { return $this->toString(); }
}
