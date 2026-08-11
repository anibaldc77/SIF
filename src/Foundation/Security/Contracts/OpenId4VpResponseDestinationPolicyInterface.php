<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseDestination;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;

interface OpenId4VpResponseDestinationPolicyInterface
{
    public function validate(
        OpenId4VpResponseDestination $destination,
        OpenId4VpPresentationContext $context
    ): void;
}
