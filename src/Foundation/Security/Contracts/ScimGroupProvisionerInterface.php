<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\Scim\ScimGroup;
use Sif\Foundation\Security\Scim\ScimResourceId;
interface ScimGroupProvisionerInterface
{
    public function create(ScimGroup $group): ScimGroup;
    public function replace(ScimResourceId $id, ScimGroup $group): ScimGroup;
    public function delete(ScimResourceId $id): void;
}
