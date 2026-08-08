<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
final readonly class ScimLifecyclePlan {
    /** @param list<ScimProvisioningAction> $actions */
    public function __construct(private array $actions) {}
    /** @return list<ScimProvisioningAction> */
    public function actions(): array { return $this->actions; }
}
