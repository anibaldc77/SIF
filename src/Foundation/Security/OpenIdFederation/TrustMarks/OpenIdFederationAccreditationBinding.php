<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustMarks;

use InvalidArgumentException;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditation;

final readonly class OpenIdFederationAccreditationBinding
{
    public function __construct(
        private OpenIdFederationTrustMark $trustMark,
        private CredentialAccreditation $accreditation
    ) {
        if (
            $this->trustMark->subjectEntityId()
            !== $this->accreditation->subject()->entityId()
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Trust Mark subject does not match accreditation subject.'
            );
        }

        if (
            $this->trustMark->issuerEntityId()
            !== $this->accreditation->authority()->entityId()
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Trust Mark issuer does not match accreditation authority.'
            );
        }
    }

    public function trustMark(): OpenIdFederationTrustMark
    {
        return $this->trustMark;
    }

    public function accreditation(): CredentialAccreditation
    {
        return $this->accreditation;
    }
}
