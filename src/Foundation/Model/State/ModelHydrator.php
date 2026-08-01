<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\State;

use Sif\Foundation\Model\Casting\ModelAttributeCaster;
use Sif\Foundation\Model\Metadata\ModelMetadata;

final readonly class ModelHydrator
{
    public function __construct(private ModelAttributeCaster $caster = new ModelAttributeCaster())
    {
    }

    /** @param array<string, mixed> $attributes */
    public function hydrate(ModelMetadata $metadata, array $attributes): ModelAttributeState
    {
        $state = new ModelAttributeState($metadata, $this->caster);
        $state->hydrate($attributes);

        return $state;
    }
}
