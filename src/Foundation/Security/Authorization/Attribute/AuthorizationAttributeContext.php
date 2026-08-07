<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authorization\Attribute;

final readonly class AuthorizationAttributeContext
{
    public function __construct(
        private AuthorizationAttributeBag $subject,
        private AuthorizationAttributeBag $resource = new AuthorizationAttributeBag(),
        private AuthorizationAttributeBag $environment = new AuthorizationAttributeBag()
    ) {
    }

    public function bag(
        AuthorizationAttributeScope $scope
    ): AuthorizationAttributeBag {
        return match ($scope) {
            AuthorizationAttributeScope::Subject => $this->subject,
            AuthorizationAttributeScope::Resource => $this->resource,
            AuthorizationAttributeScope::Environment => $this->environment,
        };
    }

    public function subject(): AuthorizationAttributeBag
    {
        return $this->subject;
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
