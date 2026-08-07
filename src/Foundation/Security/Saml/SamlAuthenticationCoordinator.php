<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use Sif\Foundation\Security\Contracts\SamlIdentityMapperInterface;
use Sif\Foundation\Security\Contracts\SamlSessionEstablisherInterface;

final readonly class SamlAuthenticationCoordinator
{
    public function __construct(
        private SamlIdentityMapperInterface $identityMapper,
        private SamlSessionEstablisherInterface $sessionEstablisher
    ) {
    }

    /**
     * @param array<string, scalar|list<scalar>|null> $attributes
     */
    public function authenticate(
        SamlAssertion $assertion,
        array $attributes = []
    ): SamlAuthenticationResult {
        $identity = $this->identityMapper->map(
            $assertion,
            $attributes
        );

        $this->sessionEstablisher->establish($identity);

        return new SamlAuthenticationResult(
            $identity,
            true
        );
    }
}
