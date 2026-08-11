<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnRiskAssessment;
use Sif\Foundation\Security\WebAuthn\WebAuthnRiskContext;

interface WebAuthnRiskEvaluatorInterface
{
    public function evaluate(WebAuthnRiskContext $context): WebAuthnRiskAssessment;
}
