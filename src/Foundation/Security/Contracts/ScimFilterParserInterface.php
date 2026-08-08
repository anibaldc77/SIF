<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Filter\ScimFilterNodeInterface;

interface ScimFilterParserInterface
{
    public function parse(string $expression): ScimFilterNodeInterface;
}
