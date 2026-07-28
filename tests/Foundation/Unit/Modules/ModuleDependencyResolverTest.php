<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Modules;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Modules\Contracts\ModuleInterface;
use Sif\Foundation\Modules\Exceptions\IncompatibleModuleVersionException;
use Sif\Foundation\Modules\Exceptions\MissingRequiredModuleException;
use Sif\Foundation\Modules\Exceptions\ModuleConflictException;
use Sif\Foundation\Modules\Exceptions\ModuleDependencyCycleException;
use Sif\Foundation\Modules\ModuleConflict;
use Sif\Foundation\Modules\ModuleDependency;
use Sif\Foundation\Modules\ModuleDependencyResolver;
use Sif\Foundation\Modules\ModuleDescriptor;
use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Modules\ModuleRegistry;
use Sif\Foundation\Modules\ModuleVersion;
use Sif\Foundation\Modules\VersionConstraint;

final class ModuleDependencyResolverTest extends TestCase
{
    public function testVersionConstraintSupportsDeterministicForms(): void
    {
        self::assertTrue((new VersionConstraint('*'))->matches(new ModuleVersion('9.9.9')));
        self::assertTrue((new VersionConstraint('1.2.3'))->matches(new ModuleVersion('1.2.3')));
        self::assertTrue((new VersionConstraint('>=1.2.0,<2.0.0'))->matches(new ModuleVersion('1.8.4')));
        self::assertTrue((new VersionConstraint('^1.2.3'))->matches(new ModuleVersion('1.9.0')));
        self::assertFalse((new VersionConstraint('^1.2.3'))->matches(new ModuleVersion('2.0.0')));
        self::assertTrue((new VersionConstraint('~1.2.3'))->matches(new ModuleVersion('1.2.9')));
        self::assertTrue((new VersionConstraint('1.2.*'))->matches(new ModuleVersion('1.2.99')));
        self::assertTrue((new VersionConstraint('1.x'))->matches(new ModuleVersion('1.8.0')));
    }

    public function testRequiredDependenciesAreOrderedBeforeDependents(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', required: [new ModuleDependency(new ModuleId('core'), '^1.0.0')]));
        $registry->register($this->module('other'));
        $registry->register($this->module('core', '1.4.0'));

        $analysis = (new ModuleDependencyResolver())->analyze($registry);

        self::assertSame(['other', 'core', 'app'], $this->ids($analysis->orderedDescriptors()));
        self::assertSame(['core'], $analysis->dependenciesByModule()['app']);
    }

    public function testIndependentModulesPreserveRegistrationOrder(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('zeta'));
        $registry->register($this->module('alpha'));

        $analysis = (new ModuleDependencyResolver())->analyze($registry);

        self::assertSame(['zeta', 'alpha'], $this->ids($analysis->orderedDescriptors()));
    }

    public function testMissingRequiredDependencyFails(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', required: [new ModuleDependency(new ModuleId('core'))]));

        $this->expectException(MissingRequiredModuleException::class);
        $this->expectExceptionMessage('Module "app" requires missing module "core".');

        (new ModuleDependencyResolver())->analyze($registry);
    }

    public function testIncompatibleRequiredDependencyFails(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('core', '2.0.0'));
        $registry->register($this->module('app', required: [new ModuleDependency(new ModuleId('core'), '^1.0.0')]));

        $this->expectException(IncompatibleModuleVersionException::class);
        (new ModuleDependencyResolver())->analyze($registry);
    }

    public function testMissingOrIncompatibleOptionalDependencyDoesNotFailOrCreateEdge(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('core', '2.0.0'));
        $registry->register($this->module('app', optional: [
            new ModuleDependency(new ModuleId('missing')),
            new ModuleDependency(new ModuleId('core'), '^1.0.0'),
        ]));

        $analysis = (new ModuleDependencyResolver())->analyze($registry);

        self::assertSame([], $analysis->dependenciesByModule()['app']);
    }

    public function testCompatibleOptionalDependencyInfluencesOrder(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('app', optional: [new ModuleDependency(new ModuleId('core'), '^1.0.0')]));
        $registry->register($this->module('core', '1.1.0'));

        $analysis = (new ModuleDependencyResolver())->analyze($registry);

        self::assertSame(['core', 'app'], $this->ids($analysis->orderedDescriptors()));
    }

    public function testMatchingConflictFailsResolution(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('legacy', '1.0.0'));
        $registry->register($this->module('modern', conflicts: [new ModuleConflict(new ModuleId('legacy'), '<2.0.0')]));

        $this->expectException(ModuleConflictException::class);
        $this->expectExceptionMessage('Module "modern" conflicts with registered module "legacy".');

        (new ModuleDependencyResolver())->analyze($registry);
    }

    public function testNonMatchingConflictDoesNotFail(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('legacy', '2.0.0'));
        $registry->register($this->module('modern', conflicts: [new ModuleConflict(new ModuleId('legacy'), '<2.0.0')]));

        self::assertCount(2, (new ModuleDependencyResolver())->analyze($registry)->orderedDescriptors());
    }

    public function testDependencyCycleFailsWithDeterministicParticipants(): void
    {
        $registry = new ModuleRegistry();
        $registry->register($this->module('alpha', required: [new ModuleDependency(new ModuleId('beta'))]));
        $registry->register($this->module('beta', required: [new ModuleDependency(new ModuleId('alpha'))]));

        $this->expectException(ModuleDependencyCycleException::class);
        $this->expectExceptionMessage('Module dependency cycle detected involving: alpha, beta.');

        (new ModuleDependencyResolver())->analyze($registry);
    }

    /**
     * @param list<ModuleDependency> $required
     * @param list<ModuleDependency> $optional
     * @param list<ModuleConflict> $conflicts
     */
    private function module(
        string $id,
        string $version = '1.0.0',
        array $required = [],
        array $optional = [],
        array $conflicts = [],
    ): ModuleInterface {
        return new class ($id, $version, $required, $optional, $conflicts) implements ModuleInterface {
            private ModuleDescriptor $descriptor;

            /**
             * @param list<ModuleDependency> $required
             * @param list<ModuleDependency> $optional
             * @param list<ModuleConflict> $conflicts
             */
            public function __construct(string $id, string $version, array $required, array $optional, array $conflicts)
            {
                $this->descriptor = new ModuleDescriptor(
                    new ModuleId($id),
                    new ModuleVersion($version),
                    ucfirst($id),
                    requiredDependencies: $required,
                    optionalDependencies: $optional,
                    conflicts: $conflicts,
                );
            }

            public function descriptor(): ModuleDescriptor
            {
                return $this->descriptor;
            }
        };
    }

    /**
     * @param list<ModuleDescriptor> $descriptors
     * @return list<string>
     */
    private function ids(array $descriptors): array
    {
        return array_map(static fn (ModuleDescriptor $descriptor): string => $descriptor->id()->value(), $descriptors);
    }
}
