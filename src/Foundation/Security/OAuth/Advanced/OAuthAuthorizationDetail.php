<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

final readonly class OAuthAuthorizationDetail
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private OAuthAuthorizationDetailType $type,
        private array $attributes = []
    ) {
    }

    public function type(): OAuthAuthorizationDetailType
    {
        return $this->type;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
