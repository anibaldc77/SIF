<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Planning;

use Sif\Foundation\Migration\Exceptions\CyclicMigrationDependencyException;
use Sif\Foundation\Migration\Exceptions\MissingMigrationDependencyException;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class MigrationPlanner
{
    public function compile(MigrationRegistry $registry, MigrationDirection $direction): MigrationPlan
    {
        /** @var array<string,MigrationDescriptor> $registered */
        $registered = [];
        foreach ($registry->all() as $descriptor) {
            $registered[$descriptor->id()->value()] = $descriptor;
        }

        /** @var array<string,list<string>> $edges */
        $edges = [];
        /** @var array<string,int> $indegree */
        $indegree = array_fill_keys(array_keys($registered), 0);

        foreach ($registered as $id => $descriptor) {
            foreach ($descriptor->dependencies() as $dependency) {
                $dependencyId = $dependency->value();
                if (!isset($registered[$dependencyId])) {
                    throw new MissingMigrationDependencyException(sprintf('Migration "%s" requires missing dependency "%s".', $id, $dependencyId));
                }
                $edges[$dependencyId][] = $id;
                ++$indegree[$id];
            }
        }

        foreach ($edges as &$dependents) {
            sort($dependents, SORT_STRING);
        }
        unset($dependents);

        $ordered = [];
        while (count($ordered) < count($registered)) {
            $ready = [];
            foreach ($registered as $id => $descriptor) {
                if ($indegree[$id] === 0 && !isset($ordered[$id])) {
                    $ready[] = $descriptor;
                }
            }
            if ($ready === []) {
                $remaining = [];
                foreach ($indegree as $id => $degree) {
                    if ($degree > 0) {
                        $remaining[] = $id;
                    }
                }
                sort($remaining, SORT_STRING);
                throw new CyclicMigrationDependencyException(sprintf('Migration dependency cycle detected among: %s.', implode(', ', $remaining)));
            }
            usort($ready, [self::class, 'compareDescriptors']);

            $descriptor = $ready[0];
            $id = $descriptor->id()->value();
            $ordered[$id] = $descriptor;

            foreach ($edges[$id] ?? [] as $dependent) {
                --$indegree[$dependent];
            }
        }

        $migrations = array_values($ordered);
        if ($direction->isDown()) {
            $migrations = array_reverse($migrations);
        }

        return new MigrationPlan($direction, $migrations);
    }

    private static function compareDescriptors(MigrationDescriptor $left, MigrationDescriptor $right): int
    {
        $leftVersion = $left->version()?->value();
        $rightVersion = $right->version()?->value();
        if ($leftVersion !== $rightVersion) {
            if ($leftVersion === null) {
                return 1;
            }
            if ($rightVersion === null) {
                return -1;
            }
            $versionComparison = strcmp($leftVersion, $rightVersion);
            if ($versionComparison !== 0) {
                return $versionComparison;
            }
        }
        return strcmp($left->id()->value(), $right->id()->value());
    }
}
