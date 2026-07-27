<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Memory;

use Sif\Foundation\Contracts\QueryInterface;
use Sif\Foundation\Exceptions\UnsupportedPersistenceCapabilityException;
use Sif\Foundation\Persistence\PersistenceCapability;
use Sif\Foundation\Persistence\QueryCriterion;
use Sif\Foundation\Persistence\QueryOperator;
use Sif\Foundation\Persistence\SortDirection;
use Sif\Foundation\Persistence\StorageRecord;

final readonly class InMemoryQueryEvaluator
{
    /**
     * @param list<StorageRecord> $records
     *
     * @return list<StorageRecord>
     */
    public function evaluate(
        array $records,
        QueryInterface $query,
    ): array {
        $filtered = array_values(
            array_filter(
                $records,
                fn (StorageRecord $record): bool => $this->matches(
                    $record,
                    $query,
                ),
            ),
        );

        $sorted = $this->sort($filtered, $query);
        $paged = $this->paginate($sorted, $query);

        return $this->project($paged, $query);
    }

    private function matches(
        StorageRecord $record,
        QueryInterface $query,
    ): bool {
        foreach ($query->criteria()->all() as $criterion) {
            if (!$this->matchesCriterion($record, $criterion)) {
                return false;
            }
        }

        return true;
    }

    private function matchesCriterion(
        StorageRecord $record,
        QueryCriterion $criterion,
    ): bool {
        $actual = $record->get($criterion->field());
        $expected = $criterion->value();

        return match ($criterion->operator()) {
            QueryOperator::Equal => $actual === $expected,
            QueryOperator::NotEqual => $actual !== $expected,
            QueryOperator::GreaterThan => $actual > $expected,
            QueryOperator::GreaterThanOrEqual => $actual >= $expected,
            QueryOperator::LessThan => $actual < $expected,
            QueryOperator::LessThanOrEqual => $actual <= $expected,
            QueryOperator::In => in_array($actual, $expected, true),
            QueryOperator::NotIn => !in_array($actual, $expected, true),
            QueryOperator::IsNull => $actual === null,
            QueryOperator::IsNotNull => $actual !== null,
            QueryOperator::Contains => is_string($actual)
                && is_string($expected)
                && str_contains($actual, $expected),
            QueryOperator::StartsWith => is_string($actual)
                && is_string($expected)
                && str_starts_with($actual, $expected),
            QueryOperator::EndsWith => is_string($actual)
                && is_string($expected)
                && str_ends_with($actual, $expected),
        };
    }

    /**
     * @param list<StorageRecord> $records
     *
     * @return list<StorageRecord>
     */
    private function sort(
        array $records,
        QueryInterface $query,
    ): array {
        $fields = $query->sortOrder()->all();

        if ($fields === []) {
            return $records;
        }

        usort(
            $records,
            static function (
                StorageRecord $left,
                StorageRecord $right,
            ) use ($fields): int {
                foreach ($fields as $field) {
                    $comparison = $left->get($field->field())
                        <=> $right->get($field->field());

                    if ($comparison === 0) {
                        continue;
                    }

                    return $field->direction() === SortDirection::Descending
                        ? -$comparison
                        : $comparison;
                }

                return 0;
            },
        );

        return $records;
    }

    /**
     * @param list<StorageRecord> $records
     *
     * @return list<StorageRecord>
     */
    private function paginate(
        array $records,
        QueryInterface $query,
    ): array {
        $pagination = $query->pagination();

        if ($pagination === null) {
            return $records;
        }

        return array_values(
            array_slice(
                $records,
                $pagination->offset(),
                $pagination->perPage(),
            ),
        );
    }

    /**
     * @param list<StorageRecord> $records
     *
     * @return list<StorageRecord>
     */
    private function project(
        array $records,
        QueryInterface $query,
    ): array {
        $projection = $query->projection();

        if ($projection->isEmpty()) {
            return $records;
        }

        $projected = [];

        foreach ($records as $record) {
            $values = [];

            foreach ($projection->fields() as $field) {
                if ($record->has($field)) {
                    $values[$field] = $record->get($field);
                }
            }

            $projected[] = new StorageRecord($values);
        }

        return $projected;
    }
}
