<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Relation;

use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Exceptions\ModelRelationException;

final readonly class ModelRelationSynchronizer
{
    public function synchronize(BaseModel $owner, BaseModel $related, ModelRelationDefinition $definition): void
    {
        if (
            $owner::class !== $definition->ownerMetadata()->modelClass()
            || $related::class !== $definition->relatedMetadata()->modelClass()
        ) {
            throw new ModelRelationException('Relation synchronization received incompatible models.');
        }

        foreach ($definition->localAttributes() as $index => $localAttribute) {
            $value = $owner->get($localAttribute);
            if ($value === null) {
                throw new ModelRelationException(sprintf(
                    'Cannot synchronize relation from null owner attribute "%s".',
                    $localAttribute,
                ));
            }

            $related->setManagedAttribute($definition->foreignAttributes()[$index], $value);
        }
    }
}
