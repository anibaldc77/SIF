<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class VerifiedIdentityClaims
{
    /**
     * @param array<string, scalar|list<scalar>|null> $claims
     * @param list<IdentityAssuranceEvidence> $evidence
     */
    public function __construct(
        private array $claims,
        private array $evidence = []
    ) {
    }

    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function claims(): array
    {
        return $this->claims;
    }

    /**
     * @return list<IdentityAssuranceEvidence>
     */
    public function evidence(): array
    {
        return $this->evidence;
    }
}
