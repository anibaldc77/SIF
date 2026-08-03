<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

use Sif\Foundation\Http\Routing\RouteDefinition;

final readonly class CompiledRouteTable
{
    public const FORMAT_VERSION = 1;

    /** @param list<RouteDefinition> $routes */
    public function __construct(
        private array $routes,
        private string $fingerprint,
    ) {
        if (!preg_match('/^[a-f0-9]{64}$/', $fingerprint)) {
            throw new \InvalidArgumentException('Compiled route fingerprints must be lowercase SHA-256 values.');
        }
    }

    /** @return list<RouteDefinition> */ public function routes(): array { return $this->routes; }
    public function fingerprint(): string { return $this->fingerprint; }
    public function formatVersion(): int { return self::FORMAT_VERSION; }
}
