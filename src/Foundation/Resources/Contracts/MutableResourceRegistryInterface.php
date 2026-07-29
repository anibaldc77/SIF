<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contracts;

use Sif\Foundation\Resources\Registry\CompiledResourceRegistry;
use Sif\Foundation\Resources\Registry\RegisteredResource;
use Sif\Foundation\Resources\ResourceDescriptor;

interface MutableResourceRegistryInterface extends ResourceRegistryInterface
{
    public function register(ResourceDescriptor $descriptor): RegisteredResource;

    public function compile(): CompiledResourceRegistry;
}
