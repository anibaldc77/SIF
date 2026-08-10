<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

final readonly class OAuthClientMetadataValidationResult
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        private OAuthClientRegistrationMetadata $effectiveMetadata,
        private array $warnings = []
    ) {
    }

    public function effectiveMetadata(): OAuthClientRegistrationMetadata
    {
        return $this->effectiveMetadata;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
