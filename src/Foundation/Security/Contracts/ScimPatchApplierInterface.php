<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Patch\ScimPatchRequest;

interface ScimPatchApplierInterface
{
    /**
     * @param array<string, mixed> $resource
     *
     * @return array<string, mixed>
     */
    public function apply(
        array $resource,
        ScimPatchRequest $request
    ): array;
}
