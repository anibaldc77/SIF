<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Ast;

use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;

final readonly class PdoSqlProjection
{
    /** @var list<PdoSqlIdentifier> */
    private array $fields;

    /** @param iterable<PdoSqlIdentifier> $fields */
    public function __construct(iterable $fields = [])
    {
        $values = [];
        $seen = [];
        foreach ($fields as $field) {
            if (isset($seen[$field->value()])) {
                continue;
            }
            $seen[$field->value()] = true;
            $values[] = $field;
        }
        $this->fields = $values;
    }

    /** @return list<PdoSqlIdentifier> */
    public function fields(): array
    {
        return $this->fields;
    }

    public function selectsAll(): bool
    {
        return $this->fields === [];
    }
}
