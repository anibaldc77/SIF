<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpPrivacyContext
{
    /**
     * @param list<string> $requestedClaims
     * @param list<string> $allowedClaims
     * @param list<string> $sensitiveClaims
     */
    public function __construct(
        private array $requestedClaims = [],
        private array $allowedClaims = [],
        private array $sensitiveClaims = []
    ) {
    }

    /**
     * @return list<string>
     */
    public function requestedClaims(): array
    {
        return $this->requestedClaims;
    }

    /**
     * @return list<string>
     */
    public function allowedClaims(): array
    {
        return $this->allowedClaims;
    }

    /**
     * @return list<string>
     */
    public function sensitiveClaims(): array
    {
        return $this->sensitiveClaims;
    }
}
