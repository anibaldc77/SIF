<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Relation;

use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\ModelRelationException;

final class ModelRelationRegistry
{
    /** @var array<class-string, array<string, ModelRelationDefinition>> */
    private array $relations = [];

    public function register(ModelRelationDefinition $definition): void
    {
        $owner = $definition->ownerMetadata()->modelClass();
        $name = $definition->name();

        if (isset($this->relations[$owner][$name])) {
            throw new ModelRelationException(sprintf(
                'Relation "%s" is already registered for model "%s".',
                $name,
                $owner,
            ));
        }

        $this->relations[$owner][$name] = $definition;
    }

    public function count(): int
    {
        $count = 0;
        foreach ($this->relations as $relations) {
            $count += count($relations);
        }

        return $count;
    }

    /** @param class-string<BaseModel> $modelClass */
    public function get(string $modelClass, string $name): ModelRelationDefinition
    {
        return $this->relations[$modelClass][$name]
            ?? throw new ModelRelationException(sprintf(
                'Relation "%s" is not registered for model "%s".',
                $name,
                $modelClass,
            ));
    }

    /**
     * @param class-string<BaseModel> $modelClass
     * @return list<ModelRelationDefinition>
     */
    public function allFor(string $modelClass): array
    {
        return array_values($this->relations[$modelClass] ?? []);
    }
}
