<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Decision;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Cache\CredentialTrustResolutionEvidence;

final readonly class CredentialTrustDecision
{
    public function __construct(
        private CredentialTrustResolutionEvidence $evidence,
        private bool $fromCache,
        private bool $stale,
        private bool $refreshRecommended
    ) {
    }

    public function evidence(): CredentialTrustResolutionEvidence
    {
        return $this->evidence;
    }

    public function trusted(): bool
    {
        return $this->evidence->assessment()->trusted();
    }

    public function fromCache(): bool
    {
        return $this->fromCache;
    }

    public function stale(): bool
    {
        return $this->stale;
    }

    public function refreshRecommended(): bool
    {
        return $this->refreshRecommended;
    }
}
