<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OpenIdFederation\Product\OpenIdFederationProductProfile;
use Sif\Foundation\Security\OpenIdFederation\Product\OpenIdFederationProductReadinessReport;
interface OpenIdFederationProductReadinessEvaluatorInterface
{
    public function evaluate(OpenIdFederationProductProfile $profile): OpenIdFederationProductReadinessReport;
}
