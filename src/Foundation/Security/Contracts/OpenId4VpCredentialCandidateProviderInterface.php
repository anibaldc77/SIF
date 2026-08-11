<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialCandidate;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQuery;

interface OpenId4VpCredentialCandidateProviderInterface
{
    /**
     * @return list<OpenId4VpCredentialCandidate>
     */
    public function candidates(
        OpenId4VpPresentationQuery $query
    ): array;
}
