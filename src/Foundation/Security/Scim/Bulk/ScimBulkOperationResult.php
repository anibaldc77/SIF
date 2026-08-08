<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

final readonly class ScimBulkOperationResult
{
    /**
     * @param array<string, mixed>|null $response
     */
    public function __construct(
        private ScimBulkOperationType $method,
        private string $status,
        private ?string $location = null,
        private ?string $version = null,
        private ?ScimBulkId $bulkId = null,
        private ?array $response = null
    ) {
    }

    public function method(): ScimBulkOperationType
    {
        return $this->method;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function location(): ?string
    {
        return $this->location;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    public function bulkId(): ?ScimBulkId
    {
        return $this->bulkId;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function response(): ?array
    {
        return $this->response;
    }
}
