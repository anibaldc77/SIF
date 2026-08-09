<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequest;
use Sif\Foundation\Security\OAuth\Advanced\OAuthPushedAuthorizationRequestUri;

interface OAuthPushedAuthorizationRequestRepositoryInterface
{
    public function save(
        OAuthPushedAuthorizationRequest $request
    ): void;

    public function find(
        OAuthPushedAuthorizationRequestUri $requestUri
    ): ?OAuthPushedAuthorizationRequest;

    public function consume(
        OAuthPushedAuthorizationRequestUri $requestUri
    ): void;
}
