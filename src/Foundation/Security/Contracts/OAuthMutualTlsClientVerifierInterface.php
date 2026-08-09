<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientCredential;

interface OAuthMutualTlsClientVerifierInterface
{
    public function verify(
        OAuthClient $client,
        OAuthClientCredential $credential
    ): bool;
}
