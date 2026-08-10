<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class CredentialTrustContext
{
    /**
     * @param list<string> $trustedIssuers
     * @param list<string> $acceptedCredentialTypes
     */
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private array $trustedIssuers = [],
        private array $acceptedCredentialTypes = [],
        private bool $requireSignatureValidation = true,
        private bool $requireValidityWindow = true
    ) {
        if (
            $this->trustedIssuers === []
            && $this->acceptedCredentialTypes === []
        ) {
            throw new InvalidArgumentException(
                'Credential trust context requires trust criteria.'
            );
        }
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    /**
     * @return list<string>
     */
    public function trustedIssuers(): array
    {
        return $this->trustedIssuers;
    }

    /**
     * @return list<string>
     */
    public function acceptedCredentialTypes(): array
    {
        return $this->acceptedCredentialTypes;
    }

    public function requireSignatureValidation(): bool
    {
        return $this->requireSignatureValidation;
    }

    public function requireValidityWindow(): bool
    {
        return $this->requireValidityWindow;
    }
}
