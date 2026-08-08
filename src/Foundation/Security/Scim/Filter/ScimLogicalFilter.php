<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Filter;

final readonly class ScimLogicalFilter implements ScimFilterNodeInterface
{
    public function __construct(
        private string $operator,
        private ScimFilterNodeInterface $left,
        private ScimFilterNodeInterface $right
    ) {
    }

    public function operator(): string
    {
        return $this->operator;
    }

    public function left(): ScimFilterNodeInterface
    {
        return $this->left;
    }

    public function right(): ScimFilterNodeInterface
    {
        return $this->right;
    }
}
