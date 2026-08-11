<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnProductProfile;
use Sif\Foundation\Security\WebAuthn\WebAuthnProductReadinessReport;

interface WebAuthnProductReadinessEvaluatorInterface
{
    public function evaluate(
        WebAuthnProductProfile $profile
    ): WebAuthnProductReadinessReport;
}
