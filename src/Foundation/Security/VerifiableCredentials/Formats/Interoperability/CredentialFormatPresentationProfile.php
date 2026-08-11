<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;

final readonly class CredentialFormatPresentationProfile
{
    /**
     * @param list<string> $requestedClaims
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $requirementId,
        private CredentialFormatProfile $formatProfile,
        private array $requestedClaims = [],
        private array $metadata = []
    ) {
        if (trim($this->requirementId) === '') {
            throw new InvalidArgumentException(
                'Credential format presentation profile is invalid.'
            );
        }
    }

    public function requirementId(): string
    {
        return $this->requirementId;
    }

    public function formatProfile(): CredentialFormatProfile
    {
        return $this->formatProfile;
    }

    /** @return list<string> */
    public function requestedClaims(): array
    {
        return $this->requestedClaims;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
