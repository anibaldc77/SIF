<?php
declare(strict_types=1);

namespace Sif\Support\Utilities;

use Sif\Support\Exceptions\InvalidArgumentException;

/** Array operations are isolated here because PHP arrays are language-level maps. */
final class Arr
{
    public function has(array $values, string $path): bool { $marker = new \stdClass(); return $this->get($values, $path, $marker) !== $marker; }
    public function get(array $values, string $path, mixed $default = null): mixed { $current = $values; foreach (explode('.', $path) as $segment) { if (!is_array($current) || !array_key_exists($segment, $current)) { return $default; } $current = $current[$segment]; } return $current; }
    public function require(array $values, string $path): mixed { $marker = new \stdClass(); $value = $this->get($values, $path, $marker); if ($value === $marker) { throw new InvalidArgumentException("Required array path '$path' is missing."); } return $value; }
}
