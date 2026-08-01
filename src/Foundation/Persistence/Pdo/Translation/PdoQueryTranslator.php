<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Translation;

use Sif\Foundation\Persistence\Pdo\Ast\PdoSelectQuery;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonOperator;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlComparisonPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlConjunction;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlInPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlLikeMode;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlLikePredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlNullPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlPagination;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlPredicate;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlProjection;
use Sif\Foundation\Persistence\Pdo\Ast\PdoSqlSortTerm;
use Sif\Foundation\Persistence\Pdo\Exception\PdoQueryTranslationException;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlIdentifier;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameter;
use Sif\Foundation\Persistence\Pdo\Sql\PdoSqlParameterBag;
use Sif\Foundation\Persistence\Query;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;

final readonly class PdoQueryTranslator
{
    public function translate(PdoSqlIdentifier $source, Query $query): PdoSelectQuery
    {
        $names = new PdoParameterNameGenerator();
        $predicates = [];
        foreach ($query->criteria()->all() as $criterion) {
            $predicates[] = $this->translateCriterion($criterion, $names);
        }

        $projection = [];
        foreach ($query->projection()->fields() as $field) {
            $projection[] = new PdoSqlIdentifier($field);
        }

        $sortTerms = [];
        foreach ($query->sortOrder()->all() as $sortField) {
            $sortTerms[] = new PdoSqlSortTerm(
                new PdoSqlIdentifier($sortField->field()),
                $sortField->direction(),
            );
        }

        $pagination = $query->pagination();

        return new PdoSelectQuery(
            source: $source,
            projection: new PdoSqlProjection($projection),
            criteria: new PdoSqlConjunction($predicates),
            sortTerms: $sortTerms,
            pagination: $pagination === null
                ? null
                : new PdoSqlPagination($pagination->perPage(), $pagination->offset()),
        );
    }

    private function translateCriterion(
        QueryCriterion $criterion,
        PdoParameterNameGenerator $names,
    ): PdoSqlPredicate {
        $field = new PdoSqlIdentifier($criterion->field());
        $operator = $criterion->operator();

        if ($operator === QueryOperator::IsNull || $operator === QueryOperator::IsNotNull) {
            return new PdoSqlNullPredicate($field, $operator === QueryOperator::IsNotNull);
        }

        if ($operator === QueryOperator::In || $operator === QueryOperator::NotIn) {
            $value = $criterion->value();
            if (!is_array($value) || $value === []) {
                throw new PdoQueryTranslationException('IN criteria require a non-empty array.');
            }

            $parameters = [];
            foreach ($value as $item) {
                $parameters[] = new PdoSqlParameter($names->next($criterion->field()), $item);
            }

            return new PdoSqlInPredicate(
                $field,
                new PdoSqlParameterBag($parameters),
                $operator === QueryOperator::NotIn,
            );
        }

        if (
            $operator === QueryOperator::Contains
            || $operator === QueryOperator::StartsWith
            || $operator === QueryOperator::EndsWith
        ) {
            $value = $criterion->value();
            if (!is_string($value)) {
                throw new PdoQueryTranslationException('LIKE criteria require string values.');
            }

            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
            $pattern = match ($operator) {
                QueryOperator::Contains => '%' . $escaped . '%',
                QueryOperator::StartsWith => $escaped . '%',
                QueryOperator::EndsWith => '%' . $escaped,
            };
            $mode = match ($operator) {
                QueryOperator::Contains => PdoSqlLikeMode::Contains,
                QueryOperator::StartsWith => PdoSqlLikeMode::StartsWith,
                QueryOperator::EndsWith => PdoSqlLikeMode::EndsWith,
            };

            return new PdoSqlLikePredicate(
                $field,
                $mode,
                new PdoSqlParameter($names->next($criterion->field()), $pattern),
            );
        }

        $comparison = match ($operator) {
            QueryOperator::Equal => PdoSqlComparisonOperator::Equal,
            QueryOperator::NotEqual => PdoSqlComparisonOperator::NotEqual,
            QueryOperator::GreaterThan => PdoSqlComparisonOperator::GreaterThan,
            QueryOperator::GreaterThanOrEqual => PdoSqlComparisonOperator::GreaterThanOrEqual,
            QueryOperator::LessThan => PdoSqlComparisonOperator::LessThan,
            QueryOperator::LessThanOrEqual => PdoSqlComparisonOperator::LessThanOrEqual,
        };

        return new PdoSqlComparisonPredicate(
            $field,
            $comparison,
            new PdoSqlParameter($names->next($criterion->field()), $criterion->value()),
        );
    }
}
