<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\Scim\Lifecycle\ScimProvisioningEvent;
interface ScimProvisioningEventPublisherInterface {
    public function publish(ScimProvisioningEvent $event): void;
}
