<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
final readonly class ScimLifecyclePlanner {
    public function __construct(private ScimLifecyclePolicy $policy) {}
    public function planUserDeletion(): ScimLifecyclePlan {
        $actions=[];
        if ($this->policy->deactivateUserBeforeDelete()) $actions[]=new ScimProvisioningAction(ScimProvisioningAction::DEACTIVATE);
        if ($this->policy->cleanupMembershipsBeforeDelete()) $actions[]=new ScimProvisioningAction(ScimProvisioningAction::REMOVE_MEMBERSHIP);
        $actions[]=new ScimProvisioningAction(ScimProvisioningAction::DELETE);
        return new ScimLifecyclePlan($actions);
    }
    public function planGroupDeletion(): ScimLifecyclePlan {
        $actions=[];
        if ($this->policy->cleanupMembershipsBeforeDelete()) $actions[]=new ScimProvisioningAction(ScimProvisioningAction::REMOVE_MEMBERSHIP);
        $actions[]=new ScimProvisioningAction(ScimProvisioningAction::DELETE);
        return new ScimLifecyclePlan($actions);
    }
}
