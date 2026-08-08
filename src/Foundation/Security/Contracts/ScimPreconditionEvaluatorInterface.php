<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Versioning\ScimPrecondition;
use Sif\Foundation\Security\Scim\Versioning\ScimPreconditionResult;
use Sif\Foundation\Security\Scim\Versioning\ScimResourceVersion;

interface ScimPreconditionEvaluatorInterface
{
    public function evaluate(
        ScimPrecondition $precondition,
        ?ScimResourceVersion $currentVersion
    ): ScimPreconditionResult;
}
