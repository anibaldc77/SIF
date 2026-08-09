<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthAuthorizationCode;

interface OAuthAuthorizationCodeRepositoryInterface
{
    public function save(OAuthAuthorizationCode $code): void;

    public function find(string $value): ?OAuthAuthorizationCode;

    public function consume(string $value): void;
}
