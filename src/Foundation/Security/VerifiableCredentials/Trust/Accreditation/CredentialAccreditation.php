<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation;

use DateTimeImmutable;
use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;

final readonly class CredentialAccreditation
{
    public function __construct(
        private string $accreditationId,
        private CredentialTrustEntityReference $subject,
        private CredentialTrustEntityReference $authority,
        private CredentialAccreditationScope $scope,
        private DateTimeImmutable $validFrom,
        private ?DateTimeImmutable $validUntil = null
    ) {
        if (trim($this->accreditationId) === '') {
            throw new InvalidArgumentException('Credential accreditation is invalid.');
        }

        if ($this->validUntil !== null && $this->validUntil < $this->validFrom) {
            throw new InvalidArgumentException('Credential accreditation validity interval is invalid.');
        }
    }

    public function accreditationId(): string
    {
        return $this->accreditationId;
    }

    public function subject(): CredentialTrustEntityReference
    {
        return $this->subject;
    }

    public function authority(): CredentialTrustEntityReference
    {
        return $this->authority;
    }

    public function scope(): CredentialAccreditationScope
    {
        return $this->scope;
    }

    public function validFrom(): DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function validUntil(): ?DateTimeImmutable
    {
        return $this->validUntil;
    }
}
