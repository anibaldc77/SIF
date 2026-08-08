<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Scim\ScimQuery;
use Sif\Foundation\Security\Scim\ScimResourceId;

interface ScimQueryExecutorInterface
{
    /**
     * @return list<array<string,mixed>>
     */
    public function search(
        string $resourceType,
        ScimQuery $query
    ): array;

    /**
     * @return array<string,mixed>|null
     */
    public function find(
        string $resourceType,
        ScimResourceId $id
    ): ?array;
}
