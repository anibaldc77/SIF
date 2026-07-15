<?php
declare(strict_types=1);

namespace Sif\Support\Utilities;

use Sif\Support\Exceptions\InvalidArgumentException;

/** Stateless service; instance methods preserve replaceability through composition. */
final class Str
{
    public function contains(string $subject, string $needle, bool $caseSensitive = true): bool { return $caseSensitive ? str_contains($subject, $needle) : str_contains(mb_strtolower($subject), mb_strtolower($needle)); }
    public function startsWith(string $subject, string $prefix): bool { return str_starts_with($subject, $prefix); }
    public function endsWith(string $subject, string $suffix): bool { return str_ends_with($subject, $suffix); }
    public function snake(string $value): string { return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', str_replace(['-', ' '], '_', $value))); }
    public function camel(string $value): string { $words = preg_split('/[_\-\s]+/', $value, -1, PREG_SPLIT_NO_EMPTY); if ($words === false || $words === []) { throw new InvalidArgumentException('String cannot be converted to camel case.'); } return strtolower(array_shift($words)).implode('', array_map(ucfirst(...), array_map(strtolower(...), $words))); }
    public function truncate(string $value, int $length, string $suffix = '…'): string { if ($length < 0) { throw new InvalidArgumentException('Length cannot be negative.'); } return mb_strlen($value) <= $length ? $value : mb_substr($value, 0, max(0, $length - mb_strlen($suffix))).$suffix; }
}
