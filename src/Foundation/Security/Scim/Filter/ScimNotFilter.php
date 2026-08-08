<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Filter;

final readonly class ScimNotFilter implements ScimFilterNodeInterface
{
    public function __construct(
        private ScimFilterNodeInterface $operand
    ) {
    }

    public function operand(): ScimFilterNodeInterface
    {
        return $this->operand;
    }
}
