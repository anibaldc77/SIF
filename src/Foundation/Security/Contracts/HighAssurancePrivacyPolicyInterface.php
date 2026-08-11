<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssurancePrivacyContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssurancePrivacyDecision;

interface HighAssurancePrivacyPolicyInterface
{
    public function decide(
        HighAssurancePrivacyContext $context
    ): HighAssurancePrivacyDecision;
}
