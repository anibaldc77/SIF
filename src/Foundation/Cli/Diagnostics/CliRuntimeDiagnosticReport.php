<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Diagnostics;

final readonly class CliRuntimeDiagnosticReport
{
    /** @var list<CliRuntimeDiagnostic> */
    private array $diagnostics;

    /** @param list<CliRuntimeDiagnostic> $diagnostics */
    public function __construct(array $diagnostics)
    {
        $this->diagnostics = array_values($diagnostics);
    }

    public function healthy(): bool
    {
        foreach ($this->diagnostics as $diagnostic) {
            if (!$diagnostic->healthy()) {
                return false;
            }
        }
        return true;
    }

    /** @return list<CliRuntimeDiagnostic> */
    public function diagnostics(): array { return $this->diagnostics; }

    /** @return array{healthy: bool, diagnostics: list<array{code: string, message: string, healthy: bool}>} */
    public function summary(): array
    {
        return [
            'healthy' => $this->healthy(),
            'diagnostics' => array_map(
                static fn (CliRuntimeDiagnostic $diagnostic): array => $diagnostic->summary(),
                $this->diagnostics,
            ),
        ];
    }
}
