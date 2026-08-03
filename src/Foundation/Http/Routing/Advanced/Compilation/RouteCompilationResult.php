<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced\Compilation;

final readonly class RouteCompilationResult
{
    /** @param list<RouteDiagnostic> $diagnostics */
    public function __construct(
        private ?CompiledRouteTable $table,
        private array $diagnostics = [],
    ) {
    }

    public function successful(): bool { return $this->table !== null && !$this->hasErrors(); }
    public function table(): ?CompiledRouteTable { return $this->table; }
    /** @return list<RouteDiagnostic> */ public function diagnostics(): array { return $this->diagnostics; }

    public function hasErrors(): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if ($diagnostic->severity() === 'error') {
                return true;
            }
        }
        return false;
    }
}
