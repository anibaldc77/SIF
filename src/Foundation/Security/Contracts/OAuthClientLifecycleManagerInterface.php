<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClientId;

interface OAuthClientLifecycleManagerInterface
{
    public function register(OAuthClient $client): OAuthClient;

    public function find(OAuthClientId $clientId): ?OAuthClient;

    public function revoke(OAuthClientId $clientId): void;
}
