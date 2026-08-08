<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Filter;

final readonly class ScimComparisonFilter implements ScimFilterNodeInterface
{
    public function __construct(
        private string $attributePath,
        private string $operator,
        private mixed $value = null
    ) {
    }

    public function attributePath(): string
    {
        return $this->attributePath;
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
