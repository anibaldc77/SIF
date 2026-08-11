<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpAuthorizationResponse
{
    /**
     * @param list<string> $vpTokens
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private array $vpTokens,
        private ?string $state = null,
        private array $metadata = []
    ) {
        if ($this->vpTokens === []) {
            throw new InvalidArgumentException(
                'OpenID4VP authorization response is invalid.'
            );
        }
    }

    /**
     * @return list<string>
     */
    public function vpTokens(): array
    {
        return $this->vpTokens;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
