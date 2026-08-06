<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Identity;

use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationState;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\PrincipalInterface;

final readonly class AuthenticatedPrincipal implements PrincipalInterface
{
    public function __construct(
        private IdentityInterface $identity,
        private PrincipalAttributeCollection $attributes,
        private AuthenticationEvidence $evidence
    ) {
    }

    public function authenticationState(): AuthenticationState
    {
        return AuthenticationState::Authenticated;
    }

    public function isAuthenticated(): bool
    {
        return true;
    }

    public function identity(): IdentityInterface
    {
        return $this->identity;
    }

    public function attributes(): PrincipalAttributeCollection
    {
        return $this->attributes;
    }

    public function evidence(): AuthenticationEvidence
    {
        return $this->evidence;
    }

    /**
     * @return array{
     *     identity_id: string,
     *     attributes: array<string, string|int|float|bool|null>,
     *     authentication: array{method: string, level: int, authenticated_at: string}
     * }
     */
    public function toArray(): array
    {
        return [
            'identity_id' => $this->identity->id()->value(),
            'attributes' => $this->attributes->toArray(),
            'authentication' => $this->evidence->toArray(),
        ];
    }
}
