<?php

declare(strict_types=1);

namespace Sif\Builder\Tests\Engine;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sif\Builder\Engine\Diagnostic\Diagnostic;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Engine\Exception\InvalidDiagnosticException;

final class DiagnosticTest extends TestCase
{
    public function testSerializesOnlySafeStructuredData(): void
    {
        $diagnostic = new Diagnostic(
            code: 'REFERENCE-001',
            severity: DiagnosticSeverity::ERROR,
            message: 'Reference target was not found.',
            source: 'engineering/WP-103.md',
            extension: 'reference.broken',
            context: ['target' => 'ADR-999', 'count' => 1],
            remediation: 'Create or correct the referenced document.',
        );

        self::assertSame([
            'code' => 'REFERENCE-001',
            'severity' => 'error',
            'message' => 'Reference target was not found.',
            'source' => 'engineering/WP-103.md',
            'extension' => 'reference.broken',
            'context' => ['count' => 1, 'target' => 'ADR-999'],
            'remediation' => 'Create or correct the referenced document.',
        ], $diagnostic->jsonSerialize());
    }

    #[DataProvider('invalidDiagnostics')]
    public function testRejectsInvalidDiagnostic(callable $factory): void
    {
        $this->expectException(InvalidDiagnosticException::class);
        $factory();
    }

    /** @return iterable<string, array{callable(): Diagnostic}> */
    public static function invalidDiagnostics(): iterable
    {
        yield 'invalid code' => [
            static fn (): Diagnostic => new Diagnostic('reference-1', DiagnosticSeverity::ERROR, 'Message'),
        ];
        yield 'empty message' => [
            static fn (): Diagnostic => new Diagnostic('ENGINE-001', DiagnosticSeverity::ERROR, '  '),
        ];
        yield 'nested context' => [
            static fn (): Diagnostic => new Diagnostic(
                'ENGINE-001',
                DiagnosticSeverity::ERROR,
                'Message',
                context: ['nested' => []],
            ),
        ];
    }
}
