<?php

declare(strict_types=1);

namespace Sif\Builder\Metadata;

final readonly class MetadataValidationResult
{
    /** @param list<MetadataValidationError> $errors */
    private function __construct(private array $errors)
    {
    }

    /** @param list<MetadataValidationError> $errors */
    public static function fromErrors(array $errors): self
    {
        foreach ($errors as $error) {
            if (!$error instanceof MetadataValidationError) {
                throw new \InvalidArgumentException('Every error must be a MetadataValidationError.');
            }
        }

        return new self(array_values($errors));
    }

    public static function valid(): self
    {
        return new self([]);
    }

    public function isValid(): bool
    {
        return $this->errors === [];
    }

    /** @return list<MetadataValidationError> */
    public function errors(): array
    {
        return $this->errors;
    }
}
