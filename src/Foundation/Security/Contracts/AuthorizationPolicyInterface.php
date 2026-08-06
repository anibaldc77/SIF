<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Authorization\AuthorizationDecision;
use Sif\Foundation\Security\Authorization\AuthorizationPolicyId;
use Sif\Foundation\Security\Authorization\AuthorizationRequest;

interface AuthorizationPolicyInterface
{
    public function id(): AuthorizationPolicyId;
    public function supports(AuthorizationRequest $request): bool;
    public function decide(AuthorizationRequest $request): AuthorizationDecision;
}
