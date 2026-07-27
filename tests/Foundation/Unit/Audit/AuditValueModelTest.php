<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Audit;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Audit\AuditAction;
use Sif\Foundation\Audit\AuditId;
use Sif\Foundation\Audit\AuditLevel;
use Sif\Foundation\Audit\AuditSubject;
use Sif\Foundation\Exceptions\InvalidAuditActionException;
use Sif\Foundation\Exceptions\InvalidAuditIdException;
use Sif\Foundation\Exceptions\InvalidAuditSubjectException;

final class AuditValueModelTest extends TestCase
{
    public function testAuditIdPreservesOpaqueValueAndSupportsEquality(): void
    {
        $first = new AuditId('audit-001');
        $same = new AuditId('audit-001');
        $other = new AuditId('audit-002');

        self::assertSame('audit-001', $first->value());
        self::assertSame('audit-001', (string) $first);
        self::assertTrue($first->equals($same));
        self::assertFalse($first->equals($other));
    }

    public function testAuditIdRejectsEmptyValue(): void
    {
        $this->expectException(InvalidAuditIdException::class);

        new AuditId('   ');
    }

    public function testAuditLevelExposesStableValuesAndOrdering(): void
    {
        self::assertSame('diagnostic', AuditLevel::Diagnostic->value);
        self::assertSame('informational', AuditLevel::Informational->value);
        self::assertSame('notice', AuditLevel::Notice->value);
        self::assertSame('warning', AuditLevel::Warning->value);
        self::assertSame('critical', AuditLevel::Critical->value);

        self::assertTrue(AuditLevel::Critical->atLeast(AuditLevel::Warning));
        self::assertTrue(AuditLevel::Warning->atLeast(AuditLevel::Warning));
        self::assertFalse(AuditLevel::Notice->atLeast(AuditLevel::Critical));
    }

    public function testAuditActionAcceptsStableSemanticNames(): void
    {
        $action = new AuditAction('document.signed');

        self::assertSame('document.signed', $action->value());
        self::assertSame('document.signed', (string) $action);
        self::assertTrue($action->equals(new AuditAction('document.signed')));
        self::assertFalse($action->equals(new AuditAction('document.updated')));
    }

    /**
     * @dataProvider invalidActionProvider
     */
    public function testAuditActionRejectsInvalidNames(string $value): void
    {
        $this->expectException(InvalidAuditActionException::class);

        new AuditAction($value);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function invalidActionProvider(): iterable
    {
        yield 'empty' => [''];
        yield 'spaces' => ['document signed'];
        yield 'uppercase' => ['Document.Signed'];
        yield 'leading separator' => ['.document'];
        yield 'trailing separator' => ['document.'];
        yield 'double separator' => ['document..signed'];
    }

    public function testAuditSubjectMayBeTypedWithoutIdentifier(): void
    {
        $subject = new AuditSubject('system');

        self::assertSame('system', $subject->type());
        self::assertNull($subject->identifier());
        self::assertFalse($subject->hasIdentifier());
    }

    public function testAuditSubjectPreservesOptionalIdentifier(): void
    {
        $subject = new AuditSubject('document', 'DOC-001');

        self::assertSame('document', $subject->type());
        self::assertSame('DOC-001', $subject->identifier());
        self::assertTrue($subject->hasIdentifier());
        self::assertTrue($subject->equals(new AuditSubject('document', 'DOC-001')));
        self::assertFalse($subject->equals(new AuditSubject('document', 'DOC-002')));
    }

    public function testAuditSubjectRejectsEmptyType(): void
    {
        $this->expectException(InvalidAuditSubjectException::class);

        new AuditSubject(' ');
    }

    public function testAuditSubjectRejectsEmptyIdentifierWhenProvided(): void
    {
        $this->expectException(InvalidAuditSubjectException::class);

        new AuditSubject('document', ' ');
    }
}
