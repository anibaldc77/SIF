<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\Scim\ScimResourceId;
use Sif\Foundation\Security\Scim\ScimUser;
interface ScimUserProvisionerInterface
{
    public function create(ScimUser $user): ScimUser;
    public function replace(ScimResourceId $id, ScimUser $user): ScimUser;
    public function delete(ScimResourceId $id): void;
}
