<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Persistence\Pdo;

use PDO;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Foundation\Persistence\ConnectionName;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnectionOwnership;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoPersistenceConnectionException;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoSqlIdentifierException;
use Sif\Foundation\Persistence\Pdo\Exception\InvalidPdoSqlParameterBagException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistenceCapabilities;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;

final class PdoPersistenceValueModelTest extends TestCase
{
    public function testPlatformNormalizesDriversAndProvidesIdentifierQuotes(): void
    {
        self::assertSame('postgresql', PdoPersistencePlatform::fromDriver('pgsql')->value());
        self::assertSame('`', PdoPersistencePlatform::mysql()->identifierQuote());
        self::assertSame('"', PdoPersistencePlatform::sqlserver()->identifierQuote());
    }

    public function testCapabilityProfilesExposePlatformLimits(): void
    {
        self::assertSame(2100, PdoPersistenceCapabilities::sqlserver()->maximumParameterCount());
        self::assertTrue(PdoPersistenceCapabilities::postgresql()->returningSupported());
        self::assertFalse(PdoPersistenceCapabilities::mysql()->returningSupported());
        self::assertTrue(PdoPersistenceCapabilities::mysql()->supportsOperator('LIKE'));
    }

    public function testConnectionImplementsProviderNeutralContractAndClosesExplicitly(): void
    {
        $connection = new PdoPersistenceConnection(
            (new ReflectionClass(PDO::class))->newInstanceWithoutConstructor(),
            new ConnectionName('testing'),
            PdoPersistencePlatform::postgresql(),
            PdoPersistenceConnectionOwnership::external(),
            PdoPersistenceCapabilities::postgresql(),
        );
        self::assertTrue($connection->isOpen());
        self::assertSame('testing', $connection->name()->value());
        self::assertSame('postgresql', $connection->summary()['platform']);
        $connection->close();
        self::assertFalse($connection->isOpen());
        $this->expectException(InvalidPdoPersistenceConnectionException::class);
        $connection->pdo();
    }

    public function testConnectionRejectsMismatchedCapabilities(): void
    {
        $this->expectException(InvalidPdoPersistenceConnectionException::class);
        new PdoPersistenceConnection(
            (new ReflectionClass(PDO::class))->newInstanceWithoutConstructor(),
            ConnectionName::default(),
            PdoPersistencePlatform::mysql(),
            PdoPersistenceConnectionOwnership::external(),
            PdoPersistenceCapabilities::postgresql(),
        );
    }

    public function testIdentifierQuotesQualifiedSegmentsPerPlatform(): void
    {
        $identifier = new PdoSqlIdentifier('audit.records');
        self::assertSame('"audit"."records"', $identifier->quoted(PdoPersistencePlatform::postgresql()));
        self::assertSame('`audit`.`records`', $identifier->quoted(PdoPersistencePlatform::mysql()));
        self::assertSame('"audit"."records"', $identifier->quoted(PdoPersistencePlatform::sqlserver()));
    }

    public function testIdentifierRejectsUnsafeInput(): void
    {
        $this->expectException(InvalidPdoSqlIdentifierException::class);
        new PdoSqlIdentifier('users; DROP TABLE users');
    }

    public function testParametersInferTypesWithoutExposingValuesInSummary(): void
    {
        $parameter = new PdoSqlParameter('user_id', 42);
        self::assertSame(':user_id', $parameter->placeholder());
        self::assertSame(PDO::PARAM_INT, $parameter->type());
        self::assertSame(['name' => 'user_id', 'placeholder' => ':user_id', 'type' => PDO::PARAM_INT], $parameter->summary());
    }

    public function testParameterBagIsImmutableAndRejectsDuplicates(): void
    {
        $bag = new PdoSqlParameterBag([new PdoSqlParameter('id', 1)]);
        $extended = $bag->with(new PdoSqlParameter('name', 'SIF'));
        self::assertCount(1, $bag);
        self::assertCount(2, $extended);
        self::assertTrue($extended->has(':name'));

        $this->expectException(InvalidPdoSqlParameterBagException::class);
        new PdoSqlParameterBag([new PdoSqlParameter('id', 1), new PdoSqlParameter(':id', 2)]);
    }
}
