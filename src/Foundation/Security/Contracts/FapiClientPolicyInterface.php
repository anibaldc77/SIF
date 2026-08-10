<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Fapi\FapiClientSecurityAssessment;
use Sif\Foundation\Security\Fapi\FapiClientSecurityRequirements;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;

interface FapiClientPolicyInterface
{
    public function validate(OAuthClient $client): void;

    public function assess(
        OAuthClient $client,
        FapiClientSecurityRequirements $requirements
    ): FapiClientSecurityAssessment;
}
