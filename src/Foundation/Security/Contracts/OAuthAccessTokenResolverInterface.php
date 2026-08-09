<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\OAuth\Advanced\OAuthSenderConstrainedAccessToken;
interface OAuthAccessTokenResolverInterface
{
    public function resolve(string $serializedAccessToken): OAuthSenderConstrainedAccessToken;
}
