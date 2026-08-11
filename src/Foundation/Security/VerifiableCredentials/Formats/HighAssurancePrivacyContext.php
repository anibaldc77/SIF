<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

final readonly class HighAssurancePrivacyContext
{
    /**
     * @param list<string> $requestedClaims
     * @param list<string> $permittedClaims
     * @param list<string> $sensitiveClaims
     */
    public function __construct(
        private array $requestedClaims = [],
        private array $permittedClaims = [],
        private array $sensitiveClaims = []
    ) {
    }

    /** @return list<string> */
    public function requestedClaims(): array
    {
        return $this->requestedClaims;
    }

    /** @return list<string> */
    public function permittedClaims(): array
    {
        return $this->permittedClaims;
    }

    /** @return list<string> */
    public function sensitiveClaims(): array
    {
        return $this->sensitiveClaims;
    }
}
