<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Migration\Pdo;

use PDO;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionName;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnectionOwnership;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationCapabilitiesException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionNameException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationConnectionOwnershipException;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationPlatformException;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationCapabilities;
use Sif\Foundation\Migration\Pdo\Platform\PdoMigrationPlatform;

final class PdoMigrationConnectionModelTest extends TestCase
{
    public function testPlatformNormalizesSupportedDriverAliases(): void
    {
        self::assertSame('postgresql', PdoMigrationPlatform::fromDriver('pgsql')->value());
        self::assertSame('mysql', PdoMigrationPlatform::fromDriver('MySQL')->value());
        self::assertSame('sqlserver', PdoMigrationPlatform::fromDriver('sqlsrv')->value());
        self::assertSame('sqlsrv', PdoMigrationPlatform::sqlserver()->driver());
    }

    public function testUnsupportedPlatformFailsClosed(): void
    {
        $this->expectException(InvalidPdoMigrationPlatformException::class);
        new PdoMigrationPlatform('sqlite');
    }

    public function testConnectionNameIsCanonicalAndRejectsPathSyntax(): void
    {
        self::assertSame('primary.migrations', (new PdoMigrationConnectionName(' primary.migrations '))->value());

        $this->expectException(InvalidPdoMigrationConnectionNameException::class);
        new PdoMigrationConnectionName('../primary');
    }

    public function testOwnershipIsClosedAndExplicit(): void
    {
        self::assertTrue(PdoMigrationConnectionOwnership::external()->externallyOwned());
        self::assertTrue(PdoMigrationConnectionOwnership::adapter()->adapterOwned());

        $this->expectException(InvalidPdoMigrationConnectionOwnershipException::class);
        new PdoMigrationConnectionOwnership('shared');
    }

    public function testGovernedProfilesExposeExpectedDifferences(): void
    {
        $postgresql = PdoMigrationCapabilities::postgresql();
        $mysql = PdoMigrationCapabilities::mysql();
        $sqlserver = PdoMigrationCapabilities::sqlserver();

        self::assertTrue($postgresql->transactionalDdl());
        self::assertFalse($mysql->transactionalDdl());
        self::assertSame('named-lock', $mysql->lockMechanism());
        self::assertSame('application-lock', $sqlserver->lockMechanism());
        self::assertTrue($sqlserver->supportsDirection(MigrationDirection::down()));
    }

    public function testContradictoryCapabilitiesFailClosed(): void
    {
        $this->expectException(InvalidPdoMigrationCapabilitiesException::class);
        new PdoMigrationCapabilities(
            PdoMigrationPlatform::mysql(),
            false,
            true,
            false,
            false,
            'named-lock',
            'session',
            true,
            [MigrationDirection::up()],
        );
    }

    public function testCapabilitiesRejectUntypedAndDuplicateDirections(): void
    {
        try {
            new PdoMigrationCapabilities(
                PdoMigrationPlatform::postgresql(),
                true,
                true,
                true,
                true,
                'advisory-lock',
                'session',
                true,
                // @phpstan-ignore-next-line
                [new \stdClass()],
            );
            self::fail('Untyped directions must be rejected.');
        } catch (InvalidPdoMigrationCapabilitiesException) {
            self::assertTrue(true);
        }

        $this->expectException(InvalidPdoMigrationCapabilitiesException::class);
        new PdoMigrationCapabilities(
            PdoMigrationPlatform::postgresql(),
            true,
            true,
            true,
            true,
            'advisory-lock',
            'session',
            true,
            [MigrationDirection::up(), MigrationDirection::up()],
        );
    }

    public function testConnectionReferenceContainsNoCredentialsOrDsnInSummary(): void
    {
        $pdo = $this->pdoReference();
        $connection = new PdoMigrationConnection(
            $pdo,
            new PdoMigrationConnectionName('primary'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::postgresql(),
        );

        self::assertSame($pdo, $connection->pdo());
        self::assertSame('primary', $connection->summary()['name']);
        self::assertSame('external', $connection->summary()['ownership']);
        self::assertArrayNotHasKey('dsn', $connection->summary());
        self::assertArrayNotHasKey('password', $connection->summary());
    }

    public function testConnectionRejectsCapabilityProfileForAnotherPlatform(): void
    {
        $this->expectException(InvalidPdoMigrationConnectionException::class);
        new PdoMigrationConnection(
            $this->pdoReference(),
            new PdoMigrationConnectionName('primary'),
            PdoMigrationPlatform::postgresql(),
            PdoMigrationConnectionOwnership::external(),
            PdoMigrationCapabilities::mysql(),
        );
    }

    private function pdoReference(): PDO
    {
        $reflection = new \ReflectionClass(PDO::class);
        /** @var PDO $pdo */
        $pdo = $reflection->newInstanceWithoutConstructor();

        return $pdo;
    }
}
