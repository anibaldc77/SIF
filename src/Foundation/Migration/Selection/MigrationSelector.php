<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Selection;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationSelectionException;
use Sif\Foundation\Migration\Exceptions\IrreversibleMigrationException;
use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryStatus;
use Sif\Foundation\Migration\History\MigrationIntegrityChecker;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Planning\MigrationPlan;
use Sif\Foundation\Migration\Planning\MigrationPlanner;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class MigrationSelector
{
    public function __construct(
        private readonly MigrationPlanner $planner = new MigrationPlanner(),
        private readonly MigrationIntegrityChecker $integrityChecker = new MigrationIntegrityChecker(),
    ) {
    }

    public function select(
        MigrationRegistry $registry,
        MigrationHistory $history,
        MigrationRequest $request,
    ): MigrationExecutionPlan {
        $this->integrityChecker->assertValid($registry, $history);

        $ordered = $this->planner->compile($registry, $request->direction())->migrations();
        $eligible = [];

        foreach ($ordered as $descriptor) {
            $record = $history->find($descriptor->id());
            if ($request->direction()->isUp()) {
                if ($record === null || $record->status()->equals(MigrationHistoryStatus::rolledBack())) {
                    $eligible[] = $descriptor;
                }
                continue;
            }

            if ($record !== null && $record->status()->equals(MigrationHistoryStatus::applied())) {
                $eligible[] = $descriptor;
            }
        }

        $selected = $this->applyTags($eligible, $request);
        $selected = $this->applyTarget($selected, $registry, $request);

        if ($request->limit() !== null) {
            $selected = array_slice($selected, 0, $request->limit());
        }

        if ($request->direction()->isDown()) {
            foreach ($selected as $descriptor) {
                if (!$descriptor->reversible()) {
                    throw new IrreversibleMigrationException(sprintf(
                        'Migration "%s" is not reversible.',
                        $descriptor->id()->value(),
                    ));
                }
            }
        }

        return new MigrationExecutionPlan(
            $request,
            new MigrationPlan($request->direction(), $selected),
        );
    }

    /**
     * @param list<MigrationDescriptor> $migrations
     * @return list<MigrationDescriptor>
     */
    private function applyTags(array $migrations, MigrationRequest $request): array
    {
        if ($request->tags() === []) {
            return $migrations;
        }

        return array_values(array_filter(
            $migrations,
            static function (MigrationDescriptor $descriptor) use ($request): bool {
                foreach ($request->tags() as $tag) {
                    if (!in_array($tag, $descriptor->tags(), true)) {
                        return false;
                    }
                }
                return true;
            },
        ));
    }

    /**
     * @param list<MigrationDescriptor> $migrations
     * @return list<MigrationDescriptor>
     */
    private function applyTarget(
        array $migrations,
        MigrationRegistry $registry,
        MigrationRequest $request,
    ): array {
        $target = $request->target();
        if ($target === null) {
            return $migrations;
        }
        if (!$registry->has($target)) {
            throw new InvalidMigrationSelectionException(sprintf(
                'Migration target "%s" is not registered.',
                $target->value(),
            ));
        }

        $selected = [];
        foreach ($migrations as $migration) {
            $selected[] = $migration;
            if ($migration->id()->equals($target)) {
                return $selected;
            }
        }

        return [];
    }
}
