<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\WebAuthn\WebAuthnAssertion;
use Sif\Foundation\Security\WebAuthn\WebAuthnAssertionValidationResult;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticationContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnCredential;

interface WebAuthnAssertionValidatorInterface
{
    public function validate(
        WebAuthnAssertion $assertion,
        WebAuthnCredential $credential,
        WebAuthnAuthenticationContext $context
    ): WebAuthnAssertionValidationResult;
}
