<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

use Sif\Foundation\Model\Exceptions\InvalidModelAttributeNameException;

final readonly class ModelAttributeName
{
    public function __construct(private string $value)
    {
        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $value)) {
            throw new InvalidModelAttributeNameException(
                sprintf('Invalid model attribute name "%s".', $value),
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }
}
