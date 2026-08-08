<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Scim\Lifecycle;
final readonly class ScimLifecyclePolicy {
    public function __construct(
        private bool $deactivateUserBeforeDelete=true,
        private bool $cleanupMembershipsBeforeDelete=true
    ) {}
    public function deactivateUserBeforeDelete(): bool { return $this->deactivateUserBeforeDelete; }
    public function cleanupMembershipsBeforeDelete(): bool { return $this->cleanupMembershipsBeforeDelete; }
}
