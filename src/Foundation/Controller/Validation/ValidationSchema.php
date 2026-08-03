<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

final readonly class ValidationSchema
{
    /** @var list<ValidationField> */
    private array $fields;

    /** @param list<ValidationField> $fields */
    public function __construct(array $fields)
    {
        $seen = [];
        foreach ($fields as $field) {
            if (isset($seen[$field->path()])) {
                throw new \InvalidArgumentException(sprintf('Duplicate validation path "%s".', $field->path()));
            }
            $seen[$field->path()] = true;
        }
        $this->fields = array_values($fields);
    }

    /** @return list<ValidationField> */
    public function fields(): array { return $this->fields; }
}
