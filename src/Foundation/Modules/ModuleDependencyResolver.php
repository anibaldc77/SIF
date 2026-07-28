<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules;

use Sif\Foundation\Modules\Contracts\ModuleDependencyResolverInterface;
use Sif\Foundation\Modules\Contracts\ModuleRegistryInterface;
use Sif\Foundation\Modules\Exceptions\IncompatibleModuleVersionException;
use Sif\Foundation\Modules\Exceptions\MissingRequiredModuleException;
use Sif\Foundation\Modules\Exceptions\ModuleConflictException;
use Sif\Foundation\Modules\Exceptions\ModuleDependencyCycleException;
use Sif\Foundation\Modules\Resolution\DependencyGraphAnalysis;

final class ModuleDependencyResolver implements ModuleDependencyResolverInterface
{
    public function analyze(ModuleRegistryInterface $registry): DependencyGraphAnalysis
    {
        $descriptors = $registry->descriptors();
        $byId = [];
        $registrationOrder = [];
        foreach ($descriptors as $index => $descriptor) {
            $id = $descriptor->id()->value();
            $byId[$id] = $descriptor;
            $registrationOrder[$id] = $index;
        }

        $dependencies = [];
        foreach ($descriptors as $descriptor) {
            $moduleId = $descriptor->id()->value();
            $dependencies[$moduleId] = [];
            foreach ($descriptor->requiredDependencies() as $dependency) {
                $target = $byId[$dependency->moduleId()->value()] ?? null;
                if ($target === null) {
                    throw MissingRequiredModuleException::forDependency($descriptor->id(), $dependency->moduleId());
                }
                $constraint = new VersionConstraint($dependency->constraint());
                if (!$constraint->matches($target->version())) {
                    throw IncompatibleModuleVersionException::forDependency(
                        $descriptor->id(),
                        $dependency->moduleId(),
                        $target->version(),
                        $constraint,
                    );
                }
                $dependencies[$moduleId][] = $dependency->moduleId()->value();
            }
            foreach ($descriptor->optionalDependencies() as $dependency) {
                $target = $byId[$dependency->moduleId()->value()] ?? null;
                if ($target !== null && (new VersionConstraint($dependency->constraint()))->matches($target->version())) {
                    $dependencies[$moduleId][] = $dependency->moduleId()->value();
                }
            }
            foreach ($descriptor->conflicts() as $conflict) {
                $target = $byId[$conflict->moduleId()->value()] ?? null;
                if ($target !== null && (new VersionConstraint($conflict->constraint()))->matches($target->version())) {
                    throw ModuleConflictException::between($descriptor->id(), $conflict->moduleId());
                }
            }
        }

        $orderedIds = $this->topologicalOrder($dependencies, $registrationOrder);
        return new DependencyGraphAnalysis(
            array_map(static fn (string $id): ModuleDescriptor => $byId[$id], $orderedIds),
            $dependencies,
        );
    }

    /**
     * @param array<string, list<string>> $dependencies
     * @param array<string, int> $registrationOrder
     * @return list<string>
     */
    private function topologicalOrder(array $dependencies, array $registrationOrder): array
    {
        $inDegree = array_fill_keys(array_keys($dependencies), 0);
        $dependents = array_fill_keys(array_keys($dependencies), []);
        foreach ($dependencies as $module => $targets) {
            foreach ($targets as $target) {
                ++$inDegree[$module];
                $dependents[$target][] = $module;
            }
        }

        $ready = [];
        foreach ($inDegree as $module => $degree) {
            if ($degree === 0) {
                $ready[] = $module;
            }
        }
        $sortReady = static function (array &$modules) use ($registrationOrder): void {
            usort($modules, static fn (string $left, string $right): int =>
                ($registrationOrder[$left] <=> $registrationOrder[$right]) ?: ($left <=> $right)
            );
        };
        $sortReady($ready);

        $ordered = [];
        while ($ready !== []) {
            $module = array_shift($ready);
            $ordered[] = $module;
            foreach ($dependents[$module] as $dependent) {
                --$inDegree[$dependent];
                if ($inDegree[$dependent] === 0) {
                    $ready[] = $dependent;
                    $sortReady($ready);
                }
            }
        }

        if (count($ordered) !== count($dependencies)) {
            $remaining = [];
            foreach ($inDegree as $module => $degree) {
                if ($degree > 0) {
                    $remaining[] = $module;
                }
            }
            usort($remaining, static fn (string $left, string $right): int =>
                ($registrationOrder[$left] <=> $registrationOrder[$right]) ?: ($left <=> $right)
            );
            throw ModuleDependencyCycleException::involving($remaining);
        }

        return $ordered;
    }
}
