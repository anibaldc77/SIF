<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Scim\Patch;

use Sif\Foundation\Security\Exceptions\InvalidScimPatchException;

final readonly class ScimPatchOperation
{
    public function __construct(
        private ScimPatchOperationType $operation,
        private ?ScimPatchPath $path = null,
        private mixed $value = null
    ) {
        if (
            $this->operation->value() === ScimPatchOperationType::REMOVE
            && $this->path === null
        ) {
            throw new InvalidScimPatchException(
                'SCIM PATCH remove operation requires a path.'
            );
        }

        if (
            in_array(
                $this->operation->value(),
                [
                    ScimPatchOperationType::ADD,
                    ScimPatchOperationType::REPLACE,
                ],
                true
            )
            && $this->value === null
        ) {
            throw new InvalidScimPatchException(
                'SCIM PATCH add/replace operation requires a value.'
            );
        }
    }

    public function operation(): ScimPatchOperationType
    {
        return $this->operation;
    }

    public function path(): ?ScimPatchPath
    {
        return $this->path;
    }

    public function value(): mixed
    {
        return $this->value;
    }
}
