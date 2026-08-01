<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Relation;

use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\ModelRelationException;
use Sif\Foundation\Model\Query\ModelQuery;
use Sif\Foundation\Model\Query\ModelQueryService;
use Sif\Foundation\Persistence\QueryOperator;

final readonly class ModelRelationLoader
{
    public function __construct(private mixed $serviceResolver)
    {
        if (!is_callable($serviceResolver)) {
            throw new ModelRelationException('Relation service resolver must be callable.');
        }
    }

    /** @return BaseModel|list<BaseModel>|null */
    public function load(BaseModel $owner, ModelRelationDefinition $definition): BaseModel|array|null
    {
        if ($owner::class !== $definition->ownerMetadata()->modelClass()) {
            throw new ModelRelationException('Relation definition is incompatible with owner model.');
        }

        $query = ModelQuery::for($definition->relatedMetadata());
        foreach ($definition->localAttributes() as $index => $localAttribute) {
            $value = $owner->get($localAttribute);
            if ($value === null) {
                return $definition->type() === ModelRelationType::HasMany ? [] : null;
            }

            $query = $query->where(
                $definition->foreignAttributes()[$index],
                QueryOperator::Equal,
                $value,
            );
        }

        $resolver = $this->serviceResolver;
        $service = $resolver($definition->relatedMetadata()->modelClass());
        if (!$service instanceof ModelQueryService) {
            throw new ModelRelationException('Relation service resolver returned an invalid service.');
        }

        return $definition->type() === ModelRelationType::HasMany
            ? $service->all($query)
            : $service->first($query);
    }
}
