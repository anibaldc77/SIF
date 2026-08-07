<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc;

use InvalidArgumentException;

final readonly class OidcAuthorizationRequest
{
    /**
     * @param list<string> $scopes
     */
    public function __construct(
        private string $authorizationEndpoint,
        private string $clientId,
        private string $redirectUri,
        private OidcState $state,
        private OidcNonce $nonce,
        private PkceCodeChallenge $codeChallenge,
        private PkceMethod $codeChallengeMethod = PkceMethod::S256,
        private array $scopes = ['openid']
    ) {
        if (!in_array('openid', $this->scopes, true)) {
            throw new InvalidArgumentException(
                'OIDC authorization request must include openid scope.'
            );
        }
    }

    public function authorizationEndpoint(): string
    {
        return $this->authorizationEndpoint;
    }

    /** @return array<string,string> */
    public function parameters(): array
    {
        return [
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => implode(' ', $this->scopes),
            'state' => $this->state->value(),
            'nonce' => $this->nonce->value(),
            'code_challenge' => $this->codeChallenge->value(),
            'code_challenge_method' => $this->codeChallengeMethod->value,
        ];
    }
}
