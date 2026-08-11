<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpCredentialSelection;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQuery;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQueryAssessment;

interface OpenId4VpPresentationQueryEvaluatorInterface
{
    public function evaluate(
        OpenId4VpPresentationQuery $query,
        OpenId4VpCredentialSelection $selection
    ): OpenId4VpPresentationQueryAssessment;
}
