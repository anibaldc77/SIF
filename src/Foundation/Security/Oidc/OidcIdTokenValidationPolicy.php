<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use DateInterval;
use InvalidArgumentException;

final readonly class OidcIdTokenValidationPolicy
{
    /**
     * @param list<string> $allowedAlgorithms
     */
    public function __construct(
        private string $issuer,
        private string $clientId,
        private array $allowedAlgorithms,
        private DateInterval $clockSkew = new DateInterval('PT0S')
    ) {
        if ($this->issuer === '') {
            throw new InvalidArgumentException(
                'OIDC ID Token issuer is required.'
            );
        }

        if ($this->clientId === '') {
            throw new InvalidArgumentException(
                'OIDC client identifier is required.'
            );
        }

        if ($this->allowedAlgorithms === []) {
            throw new InvalidArgumentException(
                'OIDC ID Token validation requires an algorithm allow-list.'
            );
        }
    }

    public function issuer(): string
    {
        return $this->issuer;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    /** @return list<string> */
    public function allowedAlgorithms(): array
    {
        return $this->allowedAlgorithms;
    }

    public function clockSkew(): DateInterval
    {
        return $this->clockSkew;
    }
}
