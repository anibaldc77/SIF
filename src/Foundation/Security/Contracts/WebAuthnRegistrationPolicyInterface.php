<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnCreationOptions;

interface WebAuthnRegistrationPolicyInterface
{
    public function validate(WebAuthnCreationOptions $options): void;
}
