<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;

interface IdentityProviderInterface
{
    public function id(): IdentityProviderId;

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult;
}
