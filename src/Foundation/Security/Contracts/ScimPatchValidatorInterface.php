<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Patch\ScimPatchRequest;

interface ScimPatchValidatorInterface
{
    public function validate(ScimPatchRequest $request): void;
}
