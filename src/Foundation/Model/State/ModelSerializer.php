<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\State;

use Sif\Foundation\Model\Casting\ModelAttributeCaster;
use Sif\Foundation\Model\Exceptions\ModelSerializationException;
use Sif\Foundation\Model\Metadata\ModelMetadata;

final readonly class ModelSerializer
{
    public function __construct(private ModelAttributeCaster $caster = new ModelAttributeCaster())
    {
    }

    /** @return array<string, mixed> */
    public function publicArray(ModelMetadata $metadata, ModelAttributeState $state): array
    {
        return $this->serialize($metadata, $state, hideHidden: true);
    }

    /** @return array<string, mixed> */
    public function storageArray(ModelMetadata $metadata, ModelAttributeState $state): array
    {
        return $this->serialize($metadata, $state, hideHidden: false);
    }

    /** @return array<string, mixed> */
    private function serialize(ModelMetadata $metadata, ModelAttributeState $state, bool $hideHidden): array
    {
        $serialized = [];
        foreach ($metadata->attributes() as $name => $definition) {
            if ($hideHidden && $definition->hidden()) {
                continue;
            }

            if (!$state->has($name)) {
                continue;
            }

            try {
                $serialized[$name] = $this->caster->serialize($definition, $state->get($name));
            } catch (\Throwable $exception) {
                throw new ModelSerializationException(
                    sprintf('Attribute "%s" could not be serialized.', $name),
                    previous: $exception,
                );
            }
        }

        return $serialized;
    }
}
