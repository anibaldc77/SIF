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
use Sif\Foundation\Migration\Pdo\Sql\PdoMigrationSqlOperationCatalog;
use Sif\Foundation\Migration\Registry\MigrationRegistry;

final class PdoMigrationAdapterCompositionTest extends TestCase
{
    public function testFactoryComposesAllPdoMigrationAdaptersWithoutInitializingHistory(): void
    {
        $connection = new PdoMigrationConnection(
            new CompositionTestPdo(),
            new PdoMigrationConnectionName('primary'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::postgresql(),
        );

        $composition = (new PdoMigrationAdapterFactory())->compose(
            $connection,
            new MigrationRegistry(),
            new PdoMigrationSqlOperationCatalog(),
        );

        self::assertSame($composition->historyStore(), $composition->historyStore());
        self::assertNull($composition->migrationMutationHandler());
        self::assertFalse($composition->transactions()->active());
    }
}

final class CompositionTestPdo extends PDO
{
    public function __construct()
    {
    }
}
