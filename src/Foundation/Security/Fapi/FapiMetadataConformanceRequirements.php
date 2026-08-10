<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiMetadataConformanceRequirements
{
    public function __construct(
        private bool $requireAuthoritativeIssuerSource = true,
        private bool $requireExactIssuerMatch = true,
        private bool $requireMetadataDerivedEndpoints = true,
        private bool $requireHttps = true
    ) {
    }

    public function requireAuthoritativeIssuerSource(): bool
    {
        return $this->requireAuthoritativeIssuerSource;
    }

    public function requireExactIssuerMatch(): bool
    {
        return $this->requireExactIssuerMatch;
    }

    public function requireMetadataDerivedEndpoints(): bool
    {
        return $this->requireMetadataDerivedEndpoints;
    }

    public function requireHttps(): bool
    {
        return $this->requireHttps;
    }
}
