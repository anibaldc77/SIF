<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Controller;

use Sif\Foundation\Security\Authorization\AuthorizationAction;
use Sif\Foundation\Security\Authorization\AuthorizationResource;

final readonly class AuthorizationRequirement
{
    public function __construct(
        private AuthorizationAction $action,
        private AuthorizationResource $resource,
    ) {
    }

    public function action(): AuthorizationAction
    {
        return $this->action;
    }

    public function resource(): AuthorizationResource
    {
        return $this->resource;
    }
}
