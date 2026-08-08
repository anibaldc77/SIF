<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

use Sif\Foundation\Security\Exceptions\InvalidScimBulkRequestException;

final readonly class ScimBulkRequest
{
    public const SCHEMA_URI = 'urn:ietf:params:scim:api:messages:2.0:BulkRequest';

    /**
     * @param list<string> $schemas
     * @param list<ScimBulkOperation> $operations
     */
    public function __construct(
        private array $schemas,
        private array $operations,
        private ?int $failOnErrors = null
    ) {
        if (!in_array(self::SCHEMA_URI, $this->schemas, true)) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk request requires BulkRequest schema.'
            );
        }

        if ($this->operations === []) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk request requires at least one operation.'
            );
        }

        if ($this->failOnErrors !== null && $this->failOnErrors < 1) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk failOnErrors must be greater than zero.'
            );
        }

        $seen = [];

        foreach ($this->operations as $operation) {
            $bulkId = $operation->bulkId();

            if ($bulkId === null) {
                continue;
            }

            if (isset($seen[$bulkId->value()])) {
                throw new InvalidScimBulkRequestException(
                    'SCIM Bulk bulkId values must be unique.'
                );
            }

            $seen[$bulkId->value()] = true;
        }
    }

    /** @return list<string> */
    public function schemas(): array
    {
        return $this->schemas;
    }

    /** @return list<ScimBulkOperation> */
    public function operations(): array
    {
        return $this->operations;
    }

    public function failOnErrors(): ?int
    {
        return $this->failOnErrors;
    }
}
