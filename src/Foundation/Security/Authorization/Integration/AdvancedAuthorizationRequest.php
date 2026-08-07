<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Integration;

use Sif\Foundation\Security\Authorization\Attribute\AuthorizationAttributeBag;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class AdvancedAuthorizationRequest
{
    public function __construct(
        private AuthenticatedPrincipal $principal,
        private AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        private AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ) {
    }

    public function principal(): AuthenticatedPrincipal
    {
        return $this->principal;
    }

    public function resource(): AuthorizationAttributeBag
    {
        return $this->resource;
    }

    public function environment(): AuthorizationAttributeBag
    {
        return $this->environment;
    }
}
