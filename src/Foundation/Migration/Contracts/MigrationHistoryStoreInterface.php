<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Contracts;

use Sif\Foundation\Migration\History\MigrationHistory;
use Sif\Foundation\Migration\History\MigrationHistoryRecord;
use Sif\Foundation\Migration\MigrationId;

interface MigrationHistoryStoreInterface
{
    public function history(): MigrationHistory;

    public function find(MigrationId $id): ?MigrationHistoryRecord;

    public function append(MigrationHistoryRecord $record): void;

    public function remove(MigrationId $id): void;
}
