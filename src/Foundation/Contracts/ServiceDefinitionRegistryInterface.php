<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ServiceDefinition;
use Sif\Foundation\Container\ServiceIdentifier;
use Sif\Foundation\Container\TaggedService;

interface ServiceDefinitionRegistryInterface
{
    public function register(ServiceDefinition $definition): void;

    public function has(ServiceIdentifier $identifier): bool;

    public function get(ServiceIdentifier $identifier): ServiceDefinition;

    /**
     * @return list<ServiceDefinition>
     */
    public function all(): array;

    /**
     * @return list<TaggedService>
     */
    public function tagged(string $tag): array;
}
