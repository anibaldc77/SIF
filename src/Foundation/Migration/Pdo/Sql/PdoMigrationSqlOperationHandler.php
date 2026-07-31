<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Sql;

use PDOException;
use Throwable;
use Sif\Foundation\Migration\Contracts\MigrationOperationHandlerInterface;
use Sif\Foundation\Migration\Execution\MigrationOperationResult;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\Pdo\Connection\PdoMigrationConnection;
use Sif\Foundation\Migration\Pdo\Exception\PdoMigrationSqlExecutionException;

final readonly class PdoMigrationSqlOperationHandler implements MigrationOperationHandlerInterface
{
    public function __construct(
        private PdoMigrationConnection $connection,
        private PdoMigrationSqlOperationCatalog $catalog,
    ) {
    }

    public function supports(MigrationDescriptor $migration): bool
    {
        return $this->catalog->has($migration->id());
    }

    public function execute(
        MigrationDescriptor $migration,
        MigrationDirection $direction,
    ): MigrationOperationResult {
        $operation = $this->catalog->get($migration->id());
        $statements = $operation->statements($direction);

        if ($statements === []) {
            return MigrationOperationResult::failure('IRREVERSIBLE_MIGRATION');
        }

        try {
            foreach ($statements as $sequence => $statement) {
                $prepared = $this->connection->pdo()->prepare($statement->sql());
                if ($prepared === false) {
                    throw new PdoMigrationSqlExecutionException(
                        sprintf(
                            'PDO could not prepare statement %d for migration "%s".',
                            $sequence + 1,
                            $migration->id()->value(),
                        ),
                    );
                }

                if (!$prepared->execute($statement->parameters())) {
                    throw new PdoMigrationSqlExecutionException(
                        sprintf(
                            'PDO could not execute statement %d for migration "%s".',
                            $sequence + 1,
                            $migration->id()->value(),
                        ),
                    );
                }
                $prepared->closeCursor();
            }
        } catch (PdoMigrationSqlExecutionException $exception) {
            throw $exception;
        } catch (PDOException $exception) {
            throw new PdoMigrationSqlExecutionException(
                sprintf('PDO SQL execution failed for migration "%s".', $migration->id()->value()),
                0,
                $exception,
            );
        } catch (Throwable $exception) {
            throw new PdoMigrationSqlExecutionException(
                sprintf('Migration SQL execution failed for migration "%s".', $migration->id()->value()),
                0,
                $exception,
            );
        }

        return MigrationOperationResult::success();
    }
}
