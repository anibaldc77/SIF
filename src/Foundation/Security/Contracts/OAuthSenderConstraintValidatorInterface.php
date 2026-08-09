<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthDPoPVerificationResult;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstrainedAccessToken;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstraintValidationResult;
interface OAuthSenderConstraintValidatorInterface
{
    public function validate(
        OAuthSenderConstrainedAccessToken $accessToken,
        OAuthDPoPVerificationResult $proof
    ): OAuthSenderConstraintValidationResult;
}
