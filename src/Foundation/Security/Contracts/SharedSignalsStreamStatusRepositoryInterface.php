<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsStreamStatus;

interface SharedSignalsStreamStatusRepositoryInterface
{
    public function find(
        string $streamId
    ): ?SharedSignalsStreamStatus;

    public function save(
        SharedSignalsStreamStatus $status
    ): void;
}
