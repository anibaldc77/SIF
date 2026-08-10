<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsStreamConfiguration;

interface SharedSignalsStreamConfigurationRepositoryInterface
{
    public function find(
        string $streamId
    ): ?SharedSignalsStreamConfiguration;

    public function save(
        SharedSignalsStreamConfiguration $configuration
    ): void;

    public function delete(
        string $streamId
    ): void;
}
