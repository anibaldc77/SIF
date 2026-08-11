<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnOperationalReadinessContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnOperationalReadinessReport;

interface WebAuthnOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        WebAuthnOperationalReadinessContext $context
    ): WebAuthnOperationalReadinessReport;
}
