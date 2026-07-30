<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\History;

use Sif\Foundation\Migration\Exceptions\MigrationIntegrityViolationException;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class MigrationIntegrityChecker
{
    public function inspect(MigrationRegistry $registry, MigrationHistory $history): MigrationIntegrityReport
    {
        $missing = [];
        $mismatches = [];
        $pending = [];

        foreach ($history->records() as $record) {
            $descriptor = $registry->get($record->id());
            if ($descriptor === null) {
                $missing[] = $record->id()->value();
                continue;
            }

            if (!$descriptor->checksum()->equals($record->checksum())) {
                $mismatches[] = $record->id()->value();
            }
        }

        foreach ($registry->all() as $descriptor) {
            $record = $history->find($descriptor->id());
            if ($record === null || $record->status()->equals(MigrationHistoryStatus::rolledBack())) {
                $pending[] = $descriptor->id()->value();
            }
        }

        return new MigrationIntegrityReport($missing, $mismatches, $pending);
    }

    public function assertValid(MigrationRegistry $registry, MigrationHistory $history): void
    {
        $report = $this->inspect($registry, $history);
        if ($report->isValid()) {
            return;
        }

        throw new MigrationIntegrityViolationException(sprintf(
            'Migration history integrity failed: %d missing registry entries and %d checksum mismatches.',
            count($report->missingFromRegistry()),
            count($report->checksumMismatches()),
        ));
    }
}
