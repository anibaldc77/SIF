<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Bulk;

use Sif\Foundation\Security\Exceptions\InvalidScimBulkRequestException;

final readonly class ScimBulkOperation
{
    /**
     * @param array<string, mixed>|null $data
     */
    public function __construct(
        private ScimBulkOperationType $method,
        private string $path,
        private ?ScimBulkId $bulkId = null,
        private ?string $version = null,
        private ?array $data = null
    ) {
        if (
            trim($this->path) === ''
            || strlen($this->path) > 2048
        ) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk operation requires a valid path.'
            );
        }

        if (
            $this->method->value() === ScimBulkOperationType::POST
            && $this->bulkId === null
        ) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk POST operation requires bulkId.'
            );
        }

        if (
            in_array(
                $this->method->value(),
                [
                    ScimBulkOperationType::POST,
                    ScimBulkOperationType::PUT,
                    ScimBulkOperationType::PATCH,
                ],
                true
            )
            && $this->data === null
        ) {
            throw new InvalidScimBulkRequestException(
                'SCIM Bulk write operation requires data.'
            );
        }
    }

    public function method(): ScimBulkOperationType
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function bulkId(): ?ScimBulkId
    {
        return $this->bulkId;
    }

    public function version(): ?string
    {
        return $this->version;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function data(): ?array
    {
        return $this->data;
    }
}
