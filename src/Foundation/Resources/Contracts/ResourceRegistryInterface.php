<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Registry\RegisteredResource;
use Sif\Foundation\Resources\ResourceDescriptor;
use Sif\Foundation\Resources\ResourceIdentifier;
use Sif\Foundation\Resources\ResourceNamespace;

interface ResourceRegistryInterface
{
    public function has(ResourceNamespace $namespace, ResourceIdentifier $identifier): bool;

    public function get(ResourceNamespace $namespace, ResourceIdentifier $identifier): ResourceDescriptor;

    /** @return list<RegisteredResource> */
    public function entries(): array;

    /** @return list<ResourceDescriptor> */
    public function resources(): array;

    public function count(): int;
}
