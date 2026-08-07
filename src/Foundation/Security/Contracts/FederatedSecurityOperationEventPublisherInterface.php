<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Operations\FederatedSecurityOperationEvent;

interface FederatedSecurityOperationEventPublisherInterface
{
    public function publish(
        FederatedSecurityOperationEvent $event
    ): void;
}
