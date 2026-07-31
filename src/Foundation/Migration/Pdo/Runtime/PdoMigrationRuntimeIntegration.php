<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Runtime;

use Sif\Foundation\Installer\Contracts\MutationHandlerInterface;
use Sif\Foundation\Migration\Pdo\Composition\PdoMigrationAdapterComposition;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;

final readonly class PdoMigrationRuntimeIntegration
{
    public function __construct(private PdoMigrationAdapterComposition $composition)
    {
    }

    public function composition(): PdoMigrationAdapterComposition
    {
        return $this->composition;
    }

    public function runtime(): MigrationRuntime
    {
        return $this->composition->runtime();
    }

    public function serviceProvider(): PdoMigrationRuntimeServiceProvider
    {
        return new PdoMigrationRuntimeServiceProvider($this);
    }

    /** @return list<MutationHandlerInterface> */
    public function installerMutationHandlers(): array
    {
        $handlers = [$this->composition->historyProvisioningHandler()];
        $migrationHandler = $this->composition->migrationMutationHandler();
        if ($migrationHandler !== null) {
            $handlers[] = $migrationHandler;
        }

        return $handlers;
    }
}
