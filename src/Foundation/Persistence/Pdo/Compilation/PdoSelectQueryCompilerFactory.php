<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryCompilationException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

final class PdoSelectQueryCompilerFactory
{
    public function create(PdoPersistencePlatform $platform): PdoSelectQueryCompiler
    {
        return match ($platform->value()) {
            'postgresql' => new PostgreSqlSelectQueryCompiler($platform),
            'mysql' => new MySqlSelectQueryCompiler($platform),
            'sqlserver' => new SqlServerSelectQueryCompiler($platform),
            default => throw new PdoQueryCompilationException('Unsupported PDO persistence platform.'),
        };
    }
}
