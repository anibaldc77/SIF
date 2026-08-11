<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResolvedPresentation;

interface OpenId4VpPresentationBindingPolicyInterface
{
    public function validate(
        OpenId4VpResolvedPresentation $presentation,
        OpenId4VpPresentationContext $context
    ): void;
}
