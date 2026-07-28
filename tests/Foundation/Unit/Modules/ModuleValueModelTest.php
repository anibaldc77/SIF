<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Modules\Exceptions\InvalidModuleDescriptorException;
use Sif\Foundation\Modules\Exceptions\InvalidModuleIdException;
use Sif\Foundation\Modules\Exceptions\InvalidModuleVersionException;
use Sif\Foundation\Modules\ModuleConflict;
use Sif\Foundation\Modules\ModuleDependency;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleVersion;

final class ModuleValueModelTest extends TestCase
{
    public function testModuleIdIsCanonicalAndCaseSensitive(): void
    {
        $id = new ModuleId('Sif.Audit-2');
        self::assertSame('Sif.Audit-2', $id->value());
        self::assertTrue($id->equals(new ModuleId('Sif.Audit-2')));
        self::assertFalse($id->equals(new ModuleId('sif.audit-2')));
    }

    /** @dataProvider invalidIds */
    public function testModuleIdRejectsInvalidValues(string $value): void
    {
        $this->expectException(InvalidModuleIdException::class);
        new ModuleId($value);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidIds(): iterable
    {
        yield 'empty' => ['']; yield 'space' => ['sif audit']; yield 'path' => ['sif/audit']; yield 'leading punctuation' => ['.audit'];
    }

    public function testSemanticVersionIsParsedAndComparedDeterministically(): void
    {
        $version = new ModuleVersion('2.1.3-alpha.1+build.8');
        self::assertSame(2, $version->major());
        self::assertSame('alpha.1', $version->preRelease());
        self::assertSame('build.8', $version->build());
        self::assertLessThan(0, $version->compareTo(new ModuleVersion('2.1.3')));
        self::assertGreaterThan(0, (new ModuleVersion('2.2.0'))->compareTo($version));
        self::assertSame(0, (new ModuleVersion('1.0.0+one'))->compareTo(new ModuleVersion('1.0.0+two')));
    }

    /** @dataProvider invalidVersions */
    public function testVersionRejectsInvalidOrAmbiguousValues(string $value): void
    {
        $this->expectException(InvalidModuleVersionException::class);
        new ModuleVersion($value);
    }

    /** @return iterable<string, array{string}> */
    public static function invalidVersions(): iterable
    {
        yield 'short' => ['1.0']; yield 'leading zero' => ['01.0.0']; yield 'numeric prerelease leading zero' => ['1.0.0-01'];
    }

    public function testDescriptorExposesImmutableDeclarativeMetadata(): void
    {
        $descriptor = new ModuleDescriptor(
            new ModuleId('sif.audit'), new ModuleVersion('2.0.0'), 'Audit', 'Audit module',
            [new ModuleDependency(new ModuleId('sif.events'), '^2.0')],
            [new ModuleDependency(new ModuleId('sif.context'))],
            [new ModuleConflict(new ModuleId('legacy.audit'))],
            ['events.dispatcher'], ['audit.emitter'], 'audit', [self::class], ['tier' => 'foundation'],
        );
        self::assertSame('sif.audit', (string) $descriptor->id());
        self::assertSame('^2.0', $descriptor->requiredDependencies()[0]->constraint());
        self::assertSame(['audit.emitter'], $descriptor->providedCapabilities());
        self::assertSame(['tier' => 'foundation'], $descriptor->diagnosticMetadata());
    }

    public function testDescriptorRejectsSelfDependency(): void
    {
        $this->expectException(InvalidModuleDescriptorException::class);
        new ModuleDescriptor(new ModuleId('sif.audit'), new ModuleVersion('1.0.0'), 'Audit', requiredDependencies: [new ModuleDependency(new ModuleId('sif.audit'))]);
    }

    public function testDescriptorRejectsContradictoryRelations(): void
    {
        $this->expectException(InvalidModuleDescriptorException::class);
        new ModuleDescriptor(new ModuleId('sif.audit'), new ModuleVersion('1.0.0'), 'Audit', requiredDependencies: [new ModuleDependency(new ModuleId('sif.events'))], conflicts: [new ModuleConflict(new ModuleId('sif.events'))]);
    }
}
