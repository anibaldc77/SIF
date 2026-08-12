<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Cache;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustChainAssessment;

final readonly class CredentialTrustResolutionEvidence
{
    public function __construct(
        private CredentialTrustChainAssessment $assessment,
        private DateTimeImmutable $checkedAt,
        private string $sourceVersion,
        private ?string $metadataFingerprint = null
    ) {
        if (trim($this->sourceVersion) === '') {
            throw new InvalidArgumentException(
                'Credential trust resolution evidence source version is invalid.'
            );
        }

        if (
            $this->metadataFingerprint !== null
            && trim($this->metadataFingerprint) === ''
        ) {
            throw new InvalidArgumentException(
                'Credential trust metadata fingerprint is invalid.'
            );
        }
    }

    public function assessment(): CredentialTrustChainAssessment
    {
        return $this->assessment;
    }

    public function checkedAt(): DateTimeImmutable
    {
        return $this->checkedAt;
    }

    public function sourceVersion(): string
    {
        return $this->sourceVersion;
    }

    public function metadataFingerprint(): ?string
    {
        return $this->metadataFingerprint;
    }
}
