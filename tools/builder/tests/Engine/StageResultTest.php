<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\BuilderContext;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\StageResult;

final class StageResultTest extends TestCase
{
    public function testReportsSuccessFromDiagnostics(): void
    {
        $context = new BuilderContext('run-001', '/repo', 'default');
        $successful = new StageResult($context);
        $failed = new StageResult(
            $context,
            new DiagnosticCollection([
                new Diagnostic('ENGINE-001', DiagnosticSeverity::ERROR, 'Stage failed.'),
            ]),
        );

        self::assertTrue($successful->isSuccessful());
        self::assertFalse($failed->isSuccessful());
    }
}
