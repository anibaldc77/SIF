<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpCredentialCandidate
{
    /**
     * @param list<string> $formats
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $credentialId,
        private array $formats = [],
        private array $attributes = []
    ) {
        if (trim($this->credentialId) === '') {
            throw new InvalidArgumentException(
                'OpenID4VP credential candidate is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    /**
     * @return list<string>
     */
    public function formats(): array
    {
        return $this->formats;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
