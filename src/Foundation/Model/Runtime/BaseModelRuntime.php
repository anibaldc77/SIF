<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Runtime;

use Sif\Foundation\Model\Casting\ModelAttributeCaster;
use Sif\Foundation\Model\Metadata\ModelMetadataRegistry;
use Sif\Foundation\Model\Relation\ModelRelationRegistry;
use Sif\Foundation\Model\State\ModelHydrator;
use Sif\Foundation\Model\State\ModelSerializer;

final readonly class BaseModelRuntime
{
    public function __construct(
        private ModelMetadataRegistry $metadata,
        private ModelRelationRegistry $relations,
        private ModelAttributeCaster $caster = new ModelAttributeCaster(),
        ?ModelHydrator $hydrator = null,
        ?ModelSerializer $serializer = null,
    ) {
        $this->hydrator = $hydrator ?? new ModelHydrator($this->caster);
        $this->serializer = $serializer ?? new ModelSerializer($this->caster);
    }

    private ModelHydrator $hydrator;

    private ModelSerializer $serializer;

    public function metadata(): ModelMetadataRegistry
    {
        return $this->metadata;
    }

    public function relations(): ModelRelationRegistry
    {
        return $this->relations;
    }

    public function caster(): ModelAttributeCaster
    {
        return $this->caster;
    }

    public function hydrator(): ModelHydrator
    {
        return $this->hydrator;
    }

    public function serializer(): ModelSerializer
    {
        return $this->serializer;
    }

    /** @return array{metadata_count: int, relation_count: int} */
    public function summary(): array
    {
        return [
            'metadata_count' => $this->metadata->count(),
            'relation_count' => $this->relations->count(),
        ];
    }
}
