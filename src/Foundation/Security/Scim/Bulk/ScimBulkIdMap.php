<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

final class ScimBulkIdMap
{
    /** @var array<string, string> */
    private array $resolved = [];

    public function register(
        ScimBulkId $bulkId,
        string $resourceLocation
    ): void {
        $this->resolved[$bulkId->value()] = $resourceLocation;
    }

    public function resolveReference(string $value): string
    {
        if (!str_starts_with($value, 'bulkId:')) {
            return $value;
        }

        $identifier = substr($value, 7);

        return $this->resolved[$identifier] ?? $value;
    }

    public function has(ScimBulkId $bulkId): bool
    {
        return isset($this->resolved[$bulkId->value()]);
    }
}
