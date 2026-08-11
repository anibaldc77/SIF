<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpVpTokenEnvelope
{
    /**
     * @param list<string> $tokens
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private array $tokens,
        private array $metadata = []
    ) {
        if ($this->tokens === []) {
            throw new InvalidArgumentException(
                'OpenID4VP VP token envelope is invalid.'
            );
        }
    }

    /**
     * @return list<string>
     */
    public function tokens(): array
    {
        return $this->tokens;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
