<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;

final readonly class GeneratorSelection
{
    /** @var list<GeneratorInterface> */
    public array $generators;

    public DiagnosticCollection $diagnostics;

    /**
     * @param list<GeneratorInterface> $generators
     */
    public function __construct(array $generators, ?DiagnosticCollection $diagnostics = null)
    {
        $this->generators = array_values($generators);
        $this->diagnostics = $diagnostics ?? new DiagnosticCollection();
    }
}
