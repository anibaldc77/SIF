<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Persistence\Pdo;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonOperator;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlConjunction;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlPagination;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlProjection;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlSortTerm;
use Sif\Foundation\Persistence\Pdo\Compilation\MySqlSelectQueryCompiler;
use Sif\Foundation\Persistence\Pdo\Compilation\PostgreSqlSelectQueryCompiler;
use Sif\Foundation\Persistence\Pdo\Compilation\PdoSelectQueryCompilerFactory;
use Sif\Foundation\Persistence\Pdo\Compilation\SqlServerSelectQueryCompiler;
use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryCompilationException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\SortDirection;

final class PdoSelectQueryCompilerTest extends TestCase
{
    public function testPostgreSqlCompilationIsDeterministic(): void
    {
        $query = $this->query();
        $compiled = new PostgreSqlSelectQueryCompiler(PdoPersistencePlatform::postgresql());

        self::assertSame(
            'SELECT "id", "name" FROM "public"."users" WHERE "status" = :p_status_0 ORDER BY "name" ASC LIMIT 25 OFFSET 50',
            $compiled->compile($query)->sql(),
        );
        self::assertSame(1, $compiled->compile($query)->parameters()->count());
    }

    public function testMySqlUsesBackticks(): void
    {
        $compiled = new MySqlSelectQueryCompiler(PdoPersistencePlatform::mysql());

        self::assertSame(
            'SELECT `id`, `name` FROM `public`.`users` WHERE `status` = :p_status_0 ORDER BY `name` ASC LIMIT 25 OFFSET 50',
            $compiled->compile($this->query())->sql(),
        );
    }

    public function testSqlServerUsesOffsetFetch(): void
    {
        $compiled = new SqlServerSelectQueryCompiler(PdoPersistencePlatform::sqlserver());

        self::assertSame(
            'SELECT "id", "name" FROM "public"."users" WHERE "status" = :p_status_0 ORDER BY "name" ASC OFFSET 50 ROWS FETCH NEXT 25 ROWS ONLY',
            $compiled->compile($this->query())->sql(),
        );
    }

    public function testSqlServerRejectsPaginationWithoutOrdering(): void
    {
        $compiler = new SqlServerSelectQueryCompiler(PdoPersistencePlatform::sqlserver());
        $query = new PdoSelectQuery(
            new PdoSqlIdentifier('users'),
            pagination: new PdoSqlPagination(10, 0),
        );

        $this->expectException(PdoQueryCompilationException::class);
        $compiler->compile($query);
    }

    public function testFactorySelectsCompilerByPlatform(): void
    {
        $factory = new PdoSelectQueryCompilerFactory();

        self::assertInstanceOf(PostgreSqlSelectQueryCompiler::class, $factory->create(PdoPersistencePlatform::postgresql()));
        self::assertInstanceOf(MySqlSelectQueryCompiler::class, $factory->create(PdoPersistencePlatform::mysql()));
        self::assertInstanceOf(SqlServerSelectQueryCompiler::class, $factory->create(PdoPersistencePlatform::sqlserver()));
    }

    private function query(): PdoSelectQuery
    {
        $parameter = new PdoSqlParameter('p_status_0', 'active');

        return new PdoSelectQuery(
            new PdoSqlIdentifier('public.users'),
            new PdoSqlProjection([
                new PdoSqlIdentifier('id'),
                new PdoSqlIdentifier('name'),
            ]),
            new PdoSqlConjunction([
                new PdoSqlComparisonPredicate(
                    new PdoSqlIdentifier('status'),
                    PdoSqlComparisonOperator::Equal,
                    $parameter,
                ),
            ]),
            [new PdoSqlSortTerm(new PdoSqlIdentifier('name'), SortDirection::Ascending)],
            new PdoSqlPagination(25, 50),
        );
    }
}
