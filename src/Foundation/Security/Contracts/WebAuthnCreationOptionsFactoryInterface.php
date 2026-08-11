<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCreationOptions;
use Sif\Foundation\Security\WebAuthn\WebAuthnRegistrationContext;

interface WebAuthnCreationOptionsFactoryInterface
{
    public function create(
        WebAuthnRegistrationContext $context
    ): WebAuthnCreationOptions;
}
