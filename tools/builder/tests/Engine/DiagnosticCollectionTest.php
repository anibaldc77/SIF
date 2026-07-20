<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticCollection;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;

final class DiagnosticCollectionTest extends TestCase
{
    public function testSortsDiagnosticsDeterministicallyAndPreservesImmutability(): void
    {
        $warning = new Diagnostic('CONFIG-002', DiagnosticSeverity::WARNING, 'Warning');
        $fatal = new Diagnostic('ENGINE-001', DiagnosticSeverity::FATAL, 'Fatal');
        $error = new Diagnostic('CONFIG-001', DiagnosticSeverity::ERROR, 'Error');

        $original = new DiagnosticCollection([$warning]);
        $extended = $original->with($error)->with($fatal);

        self::assertCount(1, $original);
        self::assertSame([$fatal, $error, $warning], $extended->all());
        self::assertTrue($extended->hasErrors());
        self::assertTrue($extended->hasSeverity(DiagnosticSeverity::FATAL));
    }

    public function testMergesCollections(): void
    {
        $first = new DiagnosticCollection([
            new Diagnostic('ENGINE-002', DiagnosticSeverity::INFO, 'Info'),
        ]);
        $second = new DiagnosticCollection([
            new Diagnostic('ENGINE-001', DiagnosticSeverity::WARNING, 'Warning'),
        ]);

        self::assertCount(2, $first->merge($second));
        self::assertCount(1, $first);
    }
}
