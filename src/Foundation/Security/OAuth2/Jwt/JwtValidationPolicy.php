<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth2\Jwt;

use DateInterval;

final readonly class JwtValidationPolicy
{
    /**
     * @param list<string> $allowedAlgorithms
     * @param list<string> $acceptedAudiences
     */
    public function __construct(
        private array $allowedAlgorithms,
        private ?string $requiredIssuer = null,
        private array $acceptedAudiences = [],
        private DateInterval $clockSkew = new DateInterval('PT0S')
    ) {
    }

    /** @return list<string> */
    public function allowedAlgorithms(): array
    {
        return $this->allowedAlgorithms;
    }

    public function requiredIssuer(): ?string
    {
        return $this->requiredIssuer;
    }

    /** @return list<string> */
    public function acceptedAudiences(): array
    {
        return $this->acceptedAudiences;
    }

    public function clockSkew(): DateInterval
    {
        return $this->clockSkew;
    }
}
