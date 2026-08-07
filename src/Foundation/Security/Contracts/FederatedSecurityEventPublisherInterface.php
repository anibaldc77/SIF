<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\Federation\FederatedSecurityEvent;

interface FederatedSecurityEventPublisherInterface
{
    public function publish(
        FederatedSecurityEvent $event
    ): void;
}
