<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation\CredentialAccreditation;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;

interface CredentialAccreditationResolverInterface
{
    /** @return list<CredentialAccreditation> */
    public function resolveFor(
        CredentialTrustEntityReference $entity
    ): array;
}
