<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstrainedAccessToken;
use Sif\Foundation\Security\OAuth\Advanced\OAuthTokenConfirmation;
interface OAuthSenderConstrainedAccessTokenIssuerInterface
{
    public function issue(
        string $subject,
        string $clientId,
        OAuthTokenConfirmation $confirmation
    ): OAuthSenderConstrainedAccessToken;
}
