<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPrivacyContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPrivacyDecision;

interface OpenId4VpPrivacyPolicyInterface
{
    public function decide(
        OpenId4VpPrivacyContext $context
    ): OpenId4VpPrivacyDecision;
}
