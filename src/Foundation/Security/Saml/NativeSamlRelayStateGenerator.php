<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use Sif\Foundation\Security\Contracts\SamlRelayStateGeneratorInterface;

final class NativeSamlRelayStateGenerator implements SamlRelayStateGeneratorInterface
{
    public function generate(): SamlRelayState
    {
        return new SamlRelayState(
            rtrim(
                strtr(
                    base64_encode(random_bytes(24)),
                    '+/',
                    '-_'
                ),
                '='
            )
        );
    }
}
