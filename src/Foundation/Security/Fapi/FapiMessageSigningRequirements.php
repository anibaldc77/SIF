<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiMessageSigningRequirements
{
    /**
     * @param list<string> $allowedRequestObjectAlgorithms
     * @param list<string> $allowedAuthorizationResponseAlgorithms
     * @param list<string> $allowedIntrospectionResponseAlgorithms
     */
    public function __construct(
        private bool $requireJar = true,
        private bool $requireJarm = true,
        private bool $requireSignedIntrospection = true,
        private array $allowedRequestObjectAlgorithms = [],
        private array $allowedAuthorizationResponseAlgorithms = [],
        private array $allowedIntrospectionResponseAlgorithms = []
    ) {
    }

    public function requireJar(): bool
    {
        return $this->requireJar;
    }

    public function requireJarm(): bool
    {
        return $this->requireJarm;
    }

    public function requireSignedIntrospection(): bool
    {
        return $this->requireSignedIntrospection;
    }

    /** @return list<string> */
    public function allowedRequestObjectAlgorithms(): array
    {
        return $this->allowedRequestObjectAlgorithms;
    }

    /** @return list<string> */
    public function allowedAuthorizationResponseAlgorithms(): array
    {
        return $this->allowedAuthorizationResponseAlgorithms;
    }

    /** @return list<string> */
    public function allowedIntrospectionResponseAlgorithms(): array
    {
        return $this->allowedIntrospectionResponseAlgorithms;
    }
}
