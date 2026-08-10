<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSenderConstraintContext
{
    public function __construct(
        private string $clientId,
        private string $resource,
        private FapiSenderConstraintMethod $method
    ) {
        if (trim($this->clientId) === '' || trim($this->resource) === '') {
            throw new InvalidArgumentException(
                'FAPI sender constraint context is invalid.'
            );
        }
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function resource(): string
    {
        return $this->resource;
    }

    public function method(): FapiSenderConstraintMethod
    {
        return $this->method;
    }
}
