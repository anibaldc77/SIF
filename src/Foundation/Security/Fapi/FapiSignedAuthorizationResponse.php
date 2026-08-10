<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

use InvalidArgumentException;

final readonly class FapiSignedAuthorizationResponse
{
    public function __construct(
        private string $serialized,
        private string $issuer,
        private string $audience,
        private string $authorizationCode,
        private string $state
    ) {
        if (
            trim($this->serialized) === ''
            || trim($this->issuer) === ''
            || trim($this->audience) === ''
            || trim($this->authorizationCode) === ''
        ) {
            throw new InvalidArgumentException(
                'FAPI signed authorization response is invalid.'
            );
        }
    }

    public function serialized(): string
    {
        return $this->serialized;
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function authorizationCode(): string
    {
        return $this->authorizationCode;
    }

    public function state(): string
    {
        return $this->state;
    }
}
