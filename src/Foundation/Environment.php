<?php
declare(strict_types=1);
namespace Sif\Foundation;
use InvalidArgumentException;
use Sif\Foundation\Contracts\EnvironmentInterface;
final readonly class Environment implements EnvironmentInterface
{
    private function __construct(private string $name) { if (trim($name)==='') { throw new InvalidArgumentException('Environment name cannot be empty.'); } }
    public static function development(): self { return new self('development'); }
    public static function testing(): self { return new self('testing'); }
    public static function staging(): self { return new self('staging'); }
    public static function production(): self { return new self('production'); }
    public static function custom(string $name): self { return new self($name); }
    public function name(): string { return $this->name; }
    public function isDevelopment(): bool { return $this->name==='development'; }
    public function isTesting(): bool { return $this->name==='testing'; }
    public function isStaging(): bool { return $this->name==='staging'; }
    public function isProduction(): bool { return $this->name==='production'; }
    public function equals(EnvironmentInterface $other): bool { return $this->name===$other->name(); }
    public function __toString(): string { return $this->name; }
}
