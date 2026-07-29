<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Steps;

use Sif\Foundation\Installer\Contracts\InstallationStepInterface;
use Sif\Foundation\Installer\Exceptions\CyclicInstallationStepDependencyException;
use Sif\Foundation\Installer\Exceptions\DuplicateInstallationStepException;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationStepException;
use Sif\Foundation\Installer\Exceptions\MissingInstallationStepDependencyException;
use Sif\Foundation\Installer\InstallationStepPlan;
use Sif\Foundation\Installer\StepDependency;

final class InstallationStepPlanner
{
    /** @param iterable<InstallationStepInterface> $steps */
    public function compile(iterable $steps): InstallationStepPlan
    {
        /** @var array<string,array{step:InstallationStepInterface,order:int,dependencies:list<StepDependency>}> $registered */
        $registered = [];
        $order = 0;
        foreach ($steps as $step) {
            if (!$step instanceof InstallationStepInterface) {
                throw new InvalidInstallationStepException('Installation step members must implement InstallationStepInterface.');
            }
            $id = $step->identifier()->value();
            if (isset($registered[$id])) {
                throw new DuplicateInstallationStepException(sprintf('Installation step "%s" is registered more than once.', $id));
            }
            $description = trim($step->description());
            if ($description === '' || strlen($description) > 2048) {
                throw new InvalidInstallationStepException(sprintf('Installation step "%s" must declare a bounded non-empty description.', $id));
            }
            $deps = [];
            $seenDeps = [];
            foreach ($step->dependencies() as $dependency) {
                if (!$dependency instanceof StepDependency) {
                    throw new InvalidInstallationStepException(sprintf('Dependencies for installation step "%s" must contain only StepDependency values.', $id));
                }
                $dependency->assertNotSelfDependency($step->identifier());
                $depId = $dependency->step()->value();
                if (isset($seenDeps[$depId])) {
                    throw new InvalidInstallationStepException(sprintf('Installation step "%s" declares dependency "%s" more than once.', $id, $depId));
                }
                $seenDeps[$depId] = true;
                $deps[] = $dependency;
            }
            $registered[$id] = ['step' => $step, 'order' => $order++, 'dependencies' => $deps];
        }

        /** @var array<string,list<string>> $edges */
        $edges = [];
        /** @var array<string,int> $indegree */
        $indegree = array_fill_keys(array_keys($registered), 0);
        foreach ($registered as $id => $entry) {
            foreach ($entry['dependencies'] as $dependency) {
                $depId = $dependency->step()->value();
                if (!isset($registered[$depId])) {
                    if ($dependency->requiredDependency()) {
                        throw new MissingInstallationStepDependencyException(sprintf('Installation step "%s" requires missing dependency "%s".', $id, $depId));
                    }
                    continue;
                }
                $edges[$depId][] = $id;
                ++$indegree[$id];
            }
        }

        $ordered = [];
        while (count($ordered) < count($registered)) {
            $ready = [];
            foreach ($registered as $id => $entry) {
                if ($indegree[$id] === 0 && !isset($ordered[$id])) {
                    $ready[] = $entry;
                }
            }
            if ($ready === []) {
                $remaining = array_keys(array_filter($indegree, static fn (int $degree): bool => $degree > 0));
                sort($remaining, SORT_STRING);
                throw new CyclicInstallationStepDependencyException(sprintf('Installation step dependency cycle detected among: %s.', implode(', ', $remaining)));
            }
            usort($ready, static function (array $left, array $right): int {
                $priority = $left['step']->priority() <=> $right['step']->priority();
                return $priority !== 0 ? $priority : $left['order'] <=> $right['order'];
            });
            foreach ($ready as $entry) {
                $id = $entry['step']->identifier()->value();
                $ordered[$id] = $entry['step'];
                foreach ($edges[$id] ?? [] as $dependent) {
                    --$indegree[$dependent];
                }
            }
        }

        return new InstallationStepPlan(array_values($ordered));
    }
}
