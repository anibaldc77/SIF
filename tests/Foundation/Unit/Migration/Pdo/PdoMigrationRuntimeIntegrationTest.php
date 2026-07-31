<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use PDO;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\Pdo\Composition\PdoMigrationAdapterFactory;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;
use Sif\Foundation\Migration\Pdo\Runtime\PdoMigrationRuntimeIntegration;
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationCatalog;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class PdoMigrationRuntimeIntegrationTest extends TestCase
{
    public function testCompositionIsExposedWithoutDatabaseSideEffects(): void
    {
        $pdo = $this->createMock(PDO::class);
        $connection = new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('runtime'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::postgresql(),
        );
        $composition = (new PdoMigrationAdapterFactory())->compose(
            $connection,
            new MigrationRegistry([]),
            new PdoMigrationSqlOperationCatalog([]),
        );

        $integration = new PdoMigrationRuntimeIntegration($composition);

        self::assertSame($composition, $integration->composition());
        self::assertSame($composition->runtime(), $integration->runtime());
        self::assertCount(1, $integration->installerMutationHandlers());
        self::assertInstanceOf(\Sif\Foundation\Migration\Pdo\Installer\PdoMigrationHistoryProvisioningHandler::class, $integration->installerMutationHandlers()[0]);
    }

    public function testProviderPublishesPdoMigrationCapabilities(): void
    {
        $pdo = $this->createMock(PDO::class);
        $connection = new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('runtime'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::postgresql(),
        );
        $composition = (new PdoMigrationAdapterFactory())->compose(
            $connection,
            new MigrationRegistry([]),
            new PdoMigrationSqlOperationCatalog([]),
        );
        $provider = (new PdoMigrationRuntimeIntegration($composition))->serviceProvider();

        $identifiers = [];
        foreach ($provider->capabilities() as $capability) {
            $identifiers[] = $capability->identifier();
        }

        self::assertSame(['migration', 'migration.pdo'], $identifiers);
    }
}
