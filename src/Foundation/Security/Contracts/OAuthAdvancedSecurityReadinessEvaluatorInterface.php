<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityProductProfile;
use Sif\Foundation\Security\OAuth\Advanced\OAuthAdvancedSecurityReadinessReport;

interface OAuthAdvancedSecurityReadinessEvaluatorInterface
{
    public function evaluate(
        OAuthAdvancedSecurityProductProfile $profile
    ): OAuthAdvancedSecurityReadinessReport;
}
