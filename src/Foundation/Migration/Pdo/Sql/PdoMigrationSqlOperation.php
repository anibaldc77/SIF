<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Pdo\Sql;

use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationId;
use Sif\Foundation\Migration\Pdo\Exception\InvalidPdoMigrationSqlOperationException;

final readonly class PdoMigrationSqlOperation
{
    /** @var list<PdoMigrationSqlStatement> */
    private array $up;

    /** @var list<PdoMigrationSqlStatement> */
    private array $down;

    /**
     * @param iterable<PdoMigrationSqlStatement> $up
     * @param iterable<PdoMigrationSqlStatement> $down
     */
    public function __construct(
        private MigrationId $id,
        iterable $up,
        iterable $down = [],
    ) {
        $this->up = $this->normalize($up, 'up');
        $this->down = $this->normalize($down, 'down');

        if ($this->up === []) {
            throw new InvalidPdoMigrationSqlOperationException(
                'PDO migration SQL operation requires at least one up statement.',
            );
        }
    }

    public function id(): MigrationId
    {
        return $this->id;
    }

    public function reversible(): bool
    {
        return $this->down !== [];
    }

    /** @return list<PdoMigrationSqlStatement> */
    public function statements(MigrationDirection $direction): array
    {
        return $direction->isUp() ? $this->up : $this->down;
    }

    /** @return array{id: string, up_statements: int, down_statements: int, reversible: bool} */
    public function summary(): array
    {
        return [
            'id' => $this->id->value(),
            'up_statements' => count($this->up),
            'down_statements' => count($this->down),
            'reversible' => $this->reversible(),
        ];
    }

    /**
     * @param iterable<PdoMigrationSqlStatement> $statements
     * @return list<PdoMigrationSqlStatement>
     */
    private function normalize(iterable $statements, string $direction): array
    {
        $normalized = [];
        foreach ($statements as $statement) {
            if (!$statement instanceof PdoMigrationSqlStatement) {
                throw new InvalidPdoMigrationSqlOperationException(
                    sprintf('PDO migration %s statements must contain only PdoMigrationSqlStatement values.', $direction),
                );
            }
            $normalized[] = $statement;
        }

        return $normalized;
    }
}
