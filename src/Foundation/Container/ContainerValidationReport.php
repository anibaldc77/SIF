<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

final readonly class ContainerValidationReport
{
    /**
     * @var list<ContainerDiagnostic>
     */
    private array $diagnostics;

    /**
     * @param list<ContainerDiagnostic> $diagnostics
     */
    public function __construct(array $diagnostics)
    {
        $this->diagnostics = array_values($diagnostics);
    }

    public function isValid(): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if (
                $diagnostic->severity()
                === ContainerDiagnosticSeverity::Error
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<ContainerDiagnostic>
     */
    public function diagnostics(): array
    {
        return $this->diagnostics;
    }

    public function count(): int
    {
        return count($this->diagnostics);
    }
}
