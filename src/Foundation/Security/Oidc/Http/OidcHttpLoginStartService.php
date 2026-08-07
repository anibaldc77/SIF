<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Http;

use Sif\Foundation\Security\Oidc\OidcAuthorizationRequestFactory;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;

final readonly class OidcHttpLoginStartService
{
    public function __construct(
        private OidcAuthorizationRequestFactory $requestFactory
    ) {
    }

    /**
     * @param list<string> $scopes
     */
    public function start(
        OidcClientRegistration $registration,
        array $scopes = ['openid']
    ): OidcLoginStartResult {
        $transaction = $this->requestFactory->create(
            $registration,
            $scopes
        );

        return new OidcLoginStartResult(
            $transaction,
            new OidcRedirectInstruction(
                $transaction->request()->authorizationEndpoint(),
                $transaction->request()->parameters()
            )
        );
    }
}
