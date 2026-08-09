<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceRequest;
use Sif\Foundation\Security\OAuth\Advanced\OAuthProtectedResourceValidationResult;
interface OAuthProtectedResourceValidatorInterface
{
    public function validate(OAuthProtectedResourceRequest $request): OAuthProtectedResourceValidationResult;
}
