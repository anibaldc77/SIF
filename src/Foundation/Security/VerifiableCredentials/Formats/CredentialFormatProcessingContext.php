<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats;

use DateTimeImmutable;

final readonly class CredentialFormatProcessingContext
{
    /**
     * @param list<string> $trustedIssuerIdentifiers
     * @param list<string> $requiredClaims
     */
    public function __construct(
        private DateTimeImmutable $evaluatedAt,
        private array $trustedIssuerIdentifiers = [],
        private array $requiredClaims = [],
        private bool $holderBindingRequired = false,
        private bool $statusValidationRequired = true
    ) {
    }

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    /**
     * @return list<string>
     */
    public function trustedIssuerIdentifiers(): array
    {
        return $this->trustedIssuerIdentifiers;
    }

    /**
     * @return list<string>
     */
    public function requiredClaims(): array
    {
        return $this->requiredClaims;
    }

    public function holderBindingRequired(): bool
    {
        return $this->holderBindingRequired;
    }

    public function statusValidationRequired(): bool
    {
        return $this->statusValidationRequired;
    }
}
