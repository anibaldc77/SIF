<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnRequestOptions;

interface WebAuthnRequestOptionsFactoryInterface
{
    public function create(
        WebAuthnAuthenticationContext $context
    ): WebAuthnRequestOptions;
}
