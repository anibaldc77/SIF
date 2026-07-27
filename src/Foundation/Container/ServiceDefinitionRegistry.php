<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

use Sif\Foundation\Contracts\ServiceDefinitionRegistryInterface;
use Sif\Foundation\Exceptions\DuplicateServiceDefinitionException;
use Sif\Foundation\Exceptions\ServiceDefinitionNotFoundException;

final class ServiceDefinitionRegistry implements
    ServiceDefinitionRegistryInterface
{
    /**
     * @var array<string, ServiceDefinition>
     */
    private array $definitions = [];

    public function register(ServiceDefinition $definition): void
    {
        $identifier = $definition->identifier()->value();

        if (isset($this->definitions[$identifier])) {
            throw new DuplicateServiceDefinitionException(
                sprintf(
                    'Service definition "%s" is already registered.',
                    $identifier,
                ),
            );
        }

        $this->definitions[$identifier] = $definition;
    }

    public function has(ServiceIdentifier $identifier): bool
    {
        return isset($this->definitions[$identifier->value()]);
    }

    public function get(ServiceIdentifier $identifier): ServiceDefinition
    {
        $value = $identifier->value();

        if (!isset($this->definitions[$value])) {
            throw new ServiceDefinitionNotFoundException(
                sprintf(
                    'Service definition "%s" is not registered.',
                    $value,
                ),
            );
        }

        return $this->definitions[$value];
    }

    /**
     * @return list<ServiceDefinition>
     */
    public function all(): array
    {
        return array_values($this->definitions);
    }

    /**
     * @return list<TaggedService>
     */
    public function tagged(string $tag): array
    {
        $results = [];
        $order = 0;

        foreach ($this->definitions as $definition) {
            foreach ($definition->tags() as $serviceTag) {
                if ($serviceTag->name() !== $tag) {
                    continue;
                }

                $results[] = new TaggedService(
                    identifier: $definition->identifier(),
                    tag: $serviceTag,
                    registrationOrder: $order,
                );
            }

            $order++;
        }

        usort(
            $results,
            static function (
                TaggedService $left,
                TaggedService $right,
            ): int {
                $priority = $right->tag()->priority()
                    <=> $left->tag()->priority();

                if ($priority !== 0) {
                    return $priority;
                }

                return $left->registrationOrder()
                    <=> $right->registrationOrder();
            },
        );

        return $results;
    }
}
