<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnRiskAssessment;
use Sif\Foundation\Security\WebAuthn\WebAuthnStepUpRequirement;

interface WebAuthnStepUpPolicyInterface
{
    public function determine(WebAuthnRiskAssessment $assessment): WebAuthnStepUpRequirement;
}
