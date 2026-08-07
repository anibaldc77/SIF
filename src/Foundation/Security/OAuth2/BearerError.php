<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2;

final readonly class BearerError
{
    public function __construct(
        private BearerErrorCode $code,
        private ?string $description = null,
        private ?string $scope = null
    ) {
    }

    public function code(): BearerErrorCode
    {
        return $this->code;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function scope(): ?string
    {
        return $this->scope;
    }

    /** @return array{error:string,error_description?:string,scope?:string} */
    public function toArray(): array
    {
        $result = ['error' => $this->code->value];

        if ($this->description !== null && $this->description !== '') {
            $result['error_description'] = $this->description;
        }

        if ($this->scope !== null && $this->scope !== '') {
            $result['scope'] = $this->scope;
        }

        return $result;
    }
}
