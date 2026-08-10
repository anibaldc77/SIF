<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

interface DisclosedClaimsExtractorInterface
{
    /**
     * @return array<string, scalar|list<scalar>|null>
     */
    public function extract(
        VerifiableCredential $credential
    ): array;
}
