<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidQueryCriterionException;

final readonly class QueryCriterion
{
    public function __construct(
        private string $field,
        private QueryOperator $operator,
        private mixed $value = null,
    ) {
        if (trim($this->field) === '') {
            throw new InvalidQueryCriterionException(
                'Query criterion field cannot be empty.',
            );
        }

        $this->assertOperatorValueCompatibility();
    }

    public function field(): string
    {
        return $this->field;
    }

    public function operator(): QueryOperator
    {
        return $this->operator;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    private function assertOperatorValueCompatibility(): void
    {
        if (
            $this->operator === QueryOperator::IsNull
            || $this->operator === QueryOperator::IsNotNull
        ) {
            if ($this->value !== null) {
                throw new InvalidQueryCriterionException(
                    sprintf(
                        'Operator "%s" does not accept a value.',
                        $this->operator->value,
                    ),
                );
            }

            return;
        }

        if (
            $this->operator === QueryOperator::In
            || $this->operator === QueryOperator::NotIn
        ) {
            if (!is_array($this->value) || $this->value === []) {
                throw new InvalidQueryCriterionException(
                    sprintf(
                        'Operator "%s" requires a non-empty array value.',
                        $this->operator->value,
                    ),
                );
            }

            return;
        }

        if (is_array($this->value)) {
            throw new InvalidQueryCriterionException(
                sprintf(
                    'Operator "%s" does not accept an array value.',
                    $this->operator->value,
                ),
            );
        }
    }
}
