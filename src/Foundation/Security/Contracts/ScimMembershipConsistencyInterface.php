<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
interface ScimMembershipConsistencyInterface {
    public function removeUserMemberships(string $userId): void;
    public function removeGroupMemberships(string $groupId): void;
}
