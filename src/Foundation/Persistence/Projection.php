<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Exceptions\InvalidProjectionException;

final readonly class Projection
{
    /**
     * @var list<string>
     */
    private array $fields;

    /**
     * @param list<string> $fields
     */
    public function __construct(array $fields = [])
    {
        $normalized = [];

        foreach ($fields as $field) {
            if (trim($field) === '') {
                throw new InvalidProjectionException(
                    'Projection fields cannot be empty.',
                );
            }

            if (in_array($field, $normalized, true)) {
                continue;
            }

            $normalized[] = $field;
        }

        $this->fields = $normalized;
    }

    /**
     * @return list<string>
     */
    public function fields(): array
    {
        return $this->fields;
    }

    public function isEmpty(): bool
    {
        return $this->fields === [];
    }

    public function includes(string $field): bool
    {
        return in_array($field, $this->fields, true);
    }
}
