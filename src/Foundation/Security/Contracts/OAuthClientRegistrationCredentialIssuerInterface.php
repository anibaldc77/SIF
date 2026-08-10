<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\Metadata\OAuthClientRegistrationCredential;

interface OAuthClientRegistrationCredentialIssuerInterface
{
    /**
     * @return list<OAuthClientRegistrationCredential>
     */
    public function issueFor(
        OAuthClient $client
    ): array;
}
