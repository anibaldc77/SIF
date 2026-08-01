<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Execution;

use PDO;
use PDOException;
use PDOStatement;
use Sif\Foundation\Persistence\Pdo\Compilation\PdoCompiledQuery;
use Sif\Foundation\Persistence\Pdo\Connection\PdoPersistenceConnection;
use Sif\Foundation\Persistence\Pdo\Exception\PdoStatementExecutionException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\ResultSet;
use Sif\Foundation\Persistence\StorageRecord;
use Throwable;

final readonly class PdoPreparedStatementExecutor
{
    public function __construct(private PdoPersistenceConnection $connection)
    {
    }

    public function execute(PdoCompiledQuery $query): PdoQueryResult
    {
        $statement = $this->prepare($query);

        try {
            $this->bindParameters($statement, $query);
            if ($statement->execute() !== true) {
                throw PdoStatementExecutionException::executionFailed();
            }

            $records = $this->adaptRows($statement);
            $affectedRows = $statement->rowCount();

            return new PdoQueryResult(new ResultSet($records), max(0, $affectedRows));
        } catch (PdoStatementExecutionException $exception) {
            throw $exception;
        } catch (PDOException $exception) {
            throw PdoStatementExecutionException::executionFailed($exception);
        } catch (Throwable $exception) {
            throw PdoStatementExecutionException::resultAdaptationFailed($exception);
        } finally {
            try {
                $statement->closeCursor();
            } catch (Throwable) {
                // Cursor cleanup must not replace the primary execution outcome.
            }
        }
    }

    private function prepare(PdoCompiledQuery $query): PDOStatement
    {
        try {
            $statement = $this->connection->pdo()->prepare($query->sql());
        } catch (PDOException $exception) {
            throw PdoStatementExecutionException::preparationFailed($exception);
        }

        if (!$statement instanceof PDOStatement) {
            throw PdoStatementExecutionException::preparationFailed();
        }

        return $statement;
    }

    private function bindParameters(PDOStatement $statement, PdoCompiledQuery $query): void
    {
        foreach ($query->parameters() as $parameter) {
            if (!$parameter instanceof PdoSqlParameter) {
                throw PdoStatementExecutionException::executionFailed();
            }

            if ($statement->bindValue($parameter->placeholder(), $parameter->value(), $parameter->type()) !== true) {
                throw PdoStatementExecutionException::executionFailed();
            }
        }
    }

    /** @return list<StorageRecord> */
    private function adaptRows(PDOStatement $statement): array
    {
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($rows)) {
            throw PdoStatementExecutionException::resultAdaptationFailed();
        }

        $records = [];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                throw PdoStatementExecutionException::resultAdaptationFailed();
            }

            $values = [];
            foreach ($row as $key => $value) {
                if (!is_string($key) || trim($key) === '') {
                    throw PdoStatementExecutionException::resultAdaptationFailed();
                }
                $values[$key] = $value;
            }
            $records[] = new StorageRecord($values);
        }

        return $records;
    }
}
