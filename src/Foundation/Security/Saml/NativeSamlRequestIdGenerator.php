<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

use Sif\Foundation\Security\Contracts\SamlRequestIdGeneratorInterface;

final class NativeSamlRequestIdGenerator implements SamlRequestIdGeneratorInterface
{
    public function generate(): SamlRequestId
    {
        return new SamlRequestId(
            '_' . bin2hex(random_bytes(20))
        );
    }
}
