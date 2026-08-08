<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Bulk\ScimBulkRequest;

interface ScimBulkValidatorInterface
{
    public function validate(ScimBulkRequest $request): void;
}
