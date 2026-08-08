<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Patch;

use Sif\Foundation\Security\Exceptions\InvalidScimPatchException;

final readonly class ScimPatchRequest
{
    public const SCHEMA_URI = 'urn:ietf:params:scim:api:messages:2.0:PatchOp';

    /**
     * @param list<string> $schemas
     * @param list<ScimPatchOperation> $operations
     */
    public function __construct(
        private array $schemas,
        private array $operations
    ) {
        if (!in_array(self::SCHEMA_URI, $this->schemas, true)) {
            throw new InvalidScimPatchException(
                'SCIM PATCH request requires the PatchOp schema.'
            );
        }

        if ($this->operations === []) {
            throw new InvalidScimPatchException(
                'SCIM PATCH request requires at least one operation.'
            );
        }
    }

    /** @return list<string> */
    public function schemas(): array
    {
        return $this->schemas;
    }

    /** @return list<ScimPatchOperation> */
    public function operations(): array
    {
        return $this->operations;
    }
}
