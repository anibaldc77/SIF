<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\Bulk\ScimBulkRequest;
use Sif\Foundation\Security\Scim\Bulk\ScimBulkResponse;

interface ScimBulkExecutorInterface
{
    public function execute(ScimBulkRequest $request): ScimBulkResponse;
}
