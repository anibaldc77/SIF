<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialCandidate;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialSelection;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQuery;

interface OpenId4VpCredentialSelectionPolicyInterface
{
    /**
     * @param list<OpenId4VpCredentialCandidate> $candidates
     */
    public function select(
        OpenId4VpPresentationQuery $query,
        array $candidates
    ): OpenId4VpCredentialSelection;
}
