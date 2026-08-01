<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Compilation;

use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlConjunction;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlInPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlLikePredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlNullPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlPredicate;
use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryCompilationException;
use Sif\Foundation\Persistence\Pdo\Platform\PdoPersistencePlatform;

abstract class AbstractPdoSelectQueryCompiler implements PdoSelectQueryCompiler
{
    final public function __construct(private readonly PdoPersistencePlatform $platform)
    {
        if (!$this->platform->equals($this->supportedPlatform())) {
            throw new PdoQueryCompilationException('Query compiler platform does not match its supported platform.');
        }
    }

    final public function compile(PdoSelectQuery $query): PdoCompiledQuery
    {
        $sql = 'SELECT ' . $this->compileProjection($query)
            . ' FROM ' . $query->source()->quoted($this->platform);

        if (!$query->criteria()->isEmpty()) {
            $sql .= ' WHERE ' . $this->compileConjunction($query->criteria());
        }

        $sortTerms = $query->sortTerms();
        if ($sortTerms !== []) {
            $terms = [];
            foreach ($sortTerms as $term) {
                $terms[] = $term->field()->quoted($this->platform) . ' ' . strtoupper($term->direction()->value);
            }
            $sql .= ' ORDER BY ' . implode(', ', $terms);
        }

        $sql = $this->compilePagination($sql, $query, $sortTerms !== []);

        return new PdoCompiledQuery($sql, $query->parameters());
    }

    abstract protected function supportedPlatform(): PdoPersistencePlatform;

    abstract protected function compilePagination(string $sql, PdoSelectQuery $query, bool $hasOrderBy): string;

    private function compileProjection(PdoSelectQuery $query): string
    {
        if ($query->projection()->selectsAll()) {
            return '*';
        }

        return implode(', ', array_map(
            fn ($field): string => $field->quoted($this->platform),
            $query->projection()->fields(),
        ));
    }

    private function compileConjunction(PdoSqlConjunction $conjunction): string
    {
        $parts = [];
        foreach ($conjunction->predicates() as $predicate) {
            $parts[] = $this->compilePredicate($predicate);
        }

        return implode(' AND ', $parts);
    }

    private function compilePredicate(PdoSqlPredicate $predicate): string
    {
        if ($predicate instanceof PdoSqlComparisonPredicate) {
            return $predicate->field()->quoted($this->platform)
                . ' ' . $predicate->operator()->value
                . ' ' . $predicate->parameter()->placeholder();
        }

        if ($predicate instanceof PdoSqlNullPredicate) {
            return $predicate->field()->quoted($this->platform)
                . ($predicate->negated() ? ' IS NOT NULL' : ' IS NULL');
        }

        if ($predicate instanceof PdoSqlInPredicate) {
            $placeholders = [];
            foreach ($predicate->parameters() as $parameter) {
                $placeholders[] = $parameter->placeholder();
            }
            return $predicate->field()->quoted($this->platform)
                . ($predicate->negated() ? ' NOT IN (' : ' IN (')
                . implode(', ', $placeholders) . ')';
        }

        if ($predicate instanceof PdoSqlLikePredicate) {
            $escape = str_replace("'", "''", $predicate->escapeCharacter());
            return $predicate->field()->quoted($this->platform)
                . ' LIKE ' . $predicate->parameter()->placeholder()
                . " ESCAPE '" . $escape . "'";
        }

        throw new PdoQueryCompilationException('Unsupported PDO query predicate.');
    }
}
