<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

final readonly class ScimBulkResponse
{
    public const SCHEMA_URI = 'urn:ietf:params:scim:api:messages:2.0:BulkResponse';

    /**
     * @param list<ScimBulkOperationResult> $operations
     */
    public function __construct(private array $operations)
    {
    }

    /** @return list<ScimBulkOperationResult> */
    public function operations(): array
    {
        return $this->operations;
    }
}
