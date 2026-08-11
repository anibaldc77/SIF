<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

use Sif\Foundation\Security\VerifiableCredentials\CredentialStatusEvidence;

final readonly class CredentialStatusResolutionDecision
{
    public function __construct(
        private CredentialStatusEvidence $result,
        private bool $fromCache,
        private bool $stale,
        private bool $refreshRecommended
    ) {
    }

    public function result(): CredentialStatusEvidence { return $this->result; }
    public function fromCache(): bool { return $this->fromCache; }
    public function stale(): bool { return $this->stale; }
    public function refreshRecommended(): bool { return $this->refreshRecommended; }
}

