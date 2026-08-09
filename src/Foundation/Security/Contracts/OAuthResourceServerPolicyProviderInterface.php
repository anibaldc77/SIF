<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthResourceServerPolicy;
interface OAuthResourceServerPolicyProviderInterface
{
    public function policyFor(string $resourceUri): OAuthResourceServerPolicy;
}
