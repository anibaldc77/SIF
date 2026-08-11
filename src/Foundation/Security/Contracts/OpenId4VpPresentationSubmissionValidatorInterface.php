<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationQuery;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationSubmission;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationSubmissionAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResolvedPresentation;

interface OpenId4VpPresentationSubmissionValidatorInterface
{
    public function validate(
        OpenId4VpPresentationSubmission $submission,
        OpenId4VpPresentationQuery $query,
        OpenId4VpResolvedPresentation $presentation
    ): OpenId4VpPresentationSubmissionAssessment;
}
