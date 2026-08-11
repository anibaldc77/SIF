<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpVerifierIdentity
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $identifier,
        private string $authenticationMethod,
        private array $attributes = []
    ) {
        if (
            trim($this->identifier) === ''
            || trim($this->authenticationMethod) === ''
        ) {
            throw new InvalidArgumentException('OpenID4VP verifier identity is invalid.');
        }
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function authenticationMethod(): string
    {
        return $this->authenticationMethod;
    }

    /**
     * @return array<string, mixed>
     */
    public function attributes(): array
    {
        return $this->attributes;
    }
}
