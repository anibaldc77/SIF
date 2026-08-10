<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataProductProfile;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataProductReadinessReport;
interface OAuthMetadataProductReadinessEvaluatorInterface
{
    public function evaluate(OAuthMetadataProductProfile $profile): OAuthMetadataProductReadinessReport;
}
