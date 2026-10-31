<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet\OpenId4VcHaipWalletAuthenticationContext;

interface OpenId4VcHaipWalletAuthenticationPolicyInterface
{
    public function assess(
        OpenId4VcHaipWalletAuthenticationContext $context
    ): OpenId4VcHaipWalletAuthenticationAssessment;
}
