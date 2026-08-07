<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Oidc\Http;

use DateTimeImmutable;
use Sif\Foundation\Security\Oidc\Federation\FederatedLoginOrchestrator;
use Sif\Foundation\Security\Oidc\Federation\FederatedLoginResult;
use Sif\Foundation\Security\Oidc\OidcAuthorizationTransaction;
use Sif\Foundation\Security\Oidc\OidcClientRegistration;
use Sif\Foundation\Security\Oidc\OidcClientSecret;

final readonly class OidcHttpCallbackService
{
    public function __construct(
        private FederatedLoginOrchestrator $orchestrator
    ) {
    }

    public function complete(
        OidcAuthorizationTransaction $transaction,
        OidcHttpCallbackRequest $request,
        OidcClientRegistration $registration,
        DateTimeImmutable $now,
        ?OidcClientSecret $clientSecret = null
    ): ?FederatedLoginResult {
        return $this->orchestrator->complete(
            $transaction,
            $request->callback(),
            $registration,
            $now,
            $clientSecret
        );
    }
}
