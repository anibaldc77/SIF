<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Installer;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Installer\Contracts\InstallationStepInterface;
use Sif\Foundation\Installer\Exceptions\CyclicInstallationStepDependencyException;
use Sif\Foundation\Installer\Exceptions\DuplicateInstallationStepException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationStepException;
use Sif\Foundation\Installer\Exceptions\MissingInstallationStepDependencyException;
use Sif\Foundation\Installer\InstallationStepIdentifier;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\RollbackPolicy;
use Sif\Foundation\Installer\StepDependency;
use Sif\Foundation\Installer\Steps\InstallationStepPlanner;

final class InstallationStepPlanningTest extends TestCase
{
    public function testOrdersDependenciesBeforeDependents(): void
    {
        $plan = (new InstallationStepPlanner())->compile([
            $this->step('configure', 10, [StepDependency::required(new InstallationStepIdentifier('prepare'))]),
            $this->step('prepare', 20),
        ]);

        self::assertSame(['prepare', 'configure'], $plan->identifiers());
    }

    public function testOrdersReadyStepsByPriorityAndRegistrationOrder(): void
    {
        $plan = (new InstallationStepPlanner())->compile([
            $this->step('third', 20),
            $this->step('first', 10),
            $this->step('second', 10),
        ]);

        self::assertSame(['first', 'second', 'third'], $plan->identifiers());
    }

    public function testOptionalMissingDependencyDoesNotBlockPlanning(): void
    {
        $plan = (new InstallationStepPlanner())->compile([
            $this->step('configure', 10, [StepDependency::optional(new InstallationStepIdentifier('optional-module'))]),
        ]);

        self::assertSame(['configure'], $plan->identifiers());
    }

    public function testRequiredMissingDependencyFails(): void
    {
        $this->expectException(MissingInstallationStepDependencyException::class);
        (new InstallationStepPlanner())->compile([
            $this->step('configure', 10, [StepDependency::required(new InstallationStepIdentifier('prepare'))]),
        ]);
    }

    public function testDuplicateStepFails(): void
    {
        $this->expectException(DuplicateInstallationStepException::class);
        (new InstallationStepPlanner())->compile([$this->step('prepare', 10), $this->step('prepare', 20)]);
    }

    public function testCycleFailsDeterministically(): void
    {
        $this->expectException(CyclicInstallationStepDependencyException::class);
        $this->expectExceptionMessage('configure, prepare');
        (new InstallationStepPlanner())->compile([
            $this->step('prepare', 10, [StepDependency::required(new InstallationStepIdentifier('configure'))]),
            $this->step('configure', 20, [StepDependency::required(new InstallationStepIdentifier('prepare'))]),
        ]);
    }

    public function testSelfDependencyFails(): void
    {
        $this->expectException(\Sif\Foundation\Installer\Exceptions\InvalidStepDependencyException::class);
        (new InstallationStepPlanner())->compile([
            $this->step('prepare', 10, [StepDependency::required(new InstallationStepIdentifier('prepare'))]),
        ]);
    }

    public function testDuplicateDependencyFails(): void
    {
        $dependency = StepDependency::required(new InstallationStepIdentifier('prepare'));
        $this->expectException(InvalidInstallationStepException::class);
        (new InstallationStepPlanner())->compile([
            $this->step('prepare', 10),
            $this->step('configure', 20, [$dependency, $dependency]),
        ]);
    }

    public function testSummaryIsSecretFreeMetadataOnly(): void
    {
        $plan = (new InstallationStepPlanner())->compile([$this->step('prepare', 10)]);
        self::assertSame([[
            'identifier' => 'prepare',
            'description' => 'Step prepare.',
            'priority' => 10,
            'mutation' => 'none',
            'idempotent' => true,
            'rollback' => 'unsupported',
        ]], $plan->summary());
    }

    public function testRejectsUntypedMembers(): void
    {
        $this->expectException(InvalidInstallationStepException::class);
        // @phpstan-ignore-next-line
        (new InstallationStepPlanner())->compile([new \stdClass()]);
    }

    /** @param list<StepDependency> $dependencies */
    private function step(string $identifier, int $priority, array $dependencies = []): InstallationStepInterface
    {
        return new class($identifier, $priority, $dependencies) implements InstallationStepInterface {
            /** @param list<StepDependency> $dependencies */
            public function __construct(private readonly string $id, private readonly int $stepPriority, private readonly array $dependencies)
            {
            }

            public function identifier(): InstallationStepIdentifier { return new InstallationStepIdentifier($this->id); }
            public function description(): string { return sprintf('Step %s.', $this->id); }
            public function priority(): int { return $this->stepPriority; }
            public function dependencies(): iterable { return $this->dependencies; }
            public function mutationClassification(): MutationClassification { return MutationClassification::none(); }
            public function idempotent(): bool { return true; }
            public function rollbackPolicy(): RollbackPolicy { return RollbackPolicy::unsupported(); }
        };
    }
}
