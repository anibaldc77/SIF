<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Exceptions\CyclicMigrationDependencyException;
use Sif\Foundation\Migration\Exceptions\DuplicateMigrationException;
use Sif\Foundation\Migration\Exceptions\InvalidMigrationRegistryException;
use Sif\Foundation\Migration\Exceptions\MissingMigrationDependencyException;
use Sif\Foundation\Migration\MigrationChecksum;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\MigrationVersion;
use Sif\Foundation\Migration\Planning\MigrationPlanner;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class MigrationRegistryPlanningTest extends TestCase
{
    public function testRegistryProvidesStableCaseSensitiveLookup(): void
    {
        $descriptor = $this->migration('Users.Create', '20260730090000');
        $registry = new MigrationRegistry([$descriptor]);
        self::assertSame($descriptor, $registry->get(new MigrationId('Users.Create')));
        self::assertNull($registry->get(new MigrationId('users.create')));
        self::assertSame(1, $registry->count());
    }

    public function testRegistryRejectsDuplicateIds(): void
    {
        $this->expectException(DuplicateMigrationException::class);
        new MigrationRegistry([$this->migration('users.create'), $this->migration('users.create')]);
    }

    public function testRegistryRejectsUntypedMembers(): void
    {
        $this->expectException(InvalidMigrationRegistryException::class);
        // @phpstan-ignore-next-line
        new MigrationRegistry([new \stdClass()]);
    }

    public function testForwardPlanRespectsDependenciesAndStableTieBreaking(): void
    {
        $registry = new MigrationRegistry([
            $this->migration('audit.create', '20260730090200', ['foundation.prepare']),
            $this->migration('users.create', '20260730090100', ['foundation.prepare']),
            $this->migration('foundation.prepare', '20260730090000'),
            $this->migration('z.unversioned'),
            $this->migration('a.unversioned'),
        ]);
        $plan = (new MigrationPlanner())->compile($registry, MigrationDirection::up());
        self::assertSame([
            'foundation.prepare', 'users.create', 'audit.create', 'a.unversioned', 'z.unversioned',
        ], $plan->identifiers());
    }

    public function testRollbackPlanIsExactReverseOfForwardPlan(): void
    {
        $registry = new MigrationRegistry([
            $this->migration('users.add-email', '20260730090200', ['users.create']),
            $this->migration('users.create', '20260730090100'),
        ]);
        $planner = new MigrationPlanner();
        self::assertSame(
            array_reverse($planner->compile($registry, MigrationDirection::up())->identifiers()),
            $planner->compile($registry, MigrationDirection::down())->identifiers(),
        );
    }

    public function testPlannerRejectsMissingDependency(): void
    {
        $registry = new MigrationRegistry([$this->migration('users.create', null, ['foundation.prepare'])]);
        $this->expectException(MissingMigrationDependencyException::class);
        (new MigrationPlanner())->compile($registry, MigrationDirection::up());
    }

    public function testPlannerRejectsCycles(): void
    {
        $registry = new MigrationRegistry([
            $this->migration('a', null, ['b']),
            $this->migration('b', null, ['a']),
        ]);
        $this->expectException(CyclicMigrationDependencyException::class);
        (new MigrationPlanner())->compile($registry, MigrationDirection::up());
    }

    public function testPlanFingerprintIsIndependentOfRegistrationOrder(): void
    {
        $a = $this->migration('a', '1.0.0');
        $b = $this->migration('b', '1.1.0', ['a']);
        $planner = new MigrationPlanner();
        $first = $planner->compile(new MigrationRegistry([$a, $b]), MigrationDirection::up());
        $second = $planner->compile(new MigrationRegistry([$b, $a]), MigrationDirection::up());
        self::assertSame($first->identifiers(), $second->identifiers());
        self::assertSame($first->fingerprint(), $second->fingerprint());
        self::assertSame(64, strlen($first->fingerprint()));
    }

    /** @param list<string> $dependencies */
    private function migration(string $id, ?string $version = null, array $dependencies = []): MigrationDescriptor
    {
        return new MigrationDescriptor(
            new MigrationId($id),
            MigrationChecksum::sha256($id . ':' . ($version ?? 'none')),
            $version === null ? null : new MigrationVersion($version),
            array_map(static fn (string $dependency): MigrationId => new MigrationId($dependency), $dependencies),
            true,
        );
    }
}
