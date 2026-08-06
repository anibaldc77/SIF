<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticatorId;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Results\AuthenticationResult;

interface AuthenticatorInterface
{
    public function id(): AuthenticatorId;

    /** @return list<CredentialType> */
    public function supportedCredentialTypes(): array;

    public function authenticate(AuthenticationRequest $request): AuthenticationResult;
}
