<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\IdentityId;

interface PersistentAuthenticationPrincipalFactoryInterface
{
    public function create(
        IdentityId $identityId,
        AuthenticationEvidence $evidence
    ): ?AuthenticatedPrincipal;
}
