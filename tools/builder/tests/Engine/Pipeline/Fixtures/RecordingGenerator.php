<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine\Pipeline\Fixtures;

use RuntimeException;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Contract\GeneratorInterface;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Extension\GenerationResult;

final readonly class RecordingGenerator implements GeneratorInterface
{
    /**
     */
    public function __construct(
        private string $identifier,
        private OperationLog $operations,
        private ?DiagnosticSeverity $severity = null,
        private bool $throws = false,
    ) {
    }

    public function id(): string
    {
        return $this->identifier;
    }

    public function generate(BuilderContext $context): GenerationResult
    {
        $this->operations->add('generator:' . $this->identifier . ':' . $context->phase->value);

        if ($this->throws) {
            throw new RuntimeException('Unsafe internal detail.');
        }

        if ($this->severity === null) {
            return new GenerationResult();
        }

        return new GenerationResult(new DiagnosticCollection([
            new Diagnostic(
                code: 'GENERATOR-101',
                severity: $this->severity,
                message: 'Generator diagnostic.',
                extension: $this->identifier,
            ),
        ]));
    }
}
