<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

final readonly class OAuthClientRegistrationSecurityPolicy
{
    /**
     * @param list<string> $allowedGrantTypes
     * @param list<string> $allowedResponseTypes
     * @param list<string> $allowedRedirectUriSchemes
     */
    public function __construct(
        private array $allowedGrantTypes = [],
        private array $allowedResponseTypes = [],
        private array $allowedRedirectUriSchemes = ['https'],
        private bool $requireSoftwareStatement = false,
        private bool $allowLoopbackRedirectUris = false
    ) {
    }

    /** @return list<string> */
    public function allowedGrantTypes(): array
    {
        return $this->allowedGrantTypes;
    }

    /** @return list<string> */
    public function allowedResponseTypes(): array
    {
        return $this->allowedResponseTypes;
    }

    /** @return list<string> */
    public function allowedRedirectUriSchemes(): array
    {
        return $this->allowedRedirectUriSchemes;
    }

    public function requireSoftwareStatement(): bool
    {
        return $this->requireSoftwareStatement;
    }

    public function allowLoopbackRedirectUris(): bool
    {
        return $this->allowLoopbackRedirectUris;
    }
}
