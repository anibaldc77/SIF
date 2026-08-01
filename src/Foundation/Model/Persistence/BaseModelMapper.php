<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Persistence;

use Closure;
use Sif\Foundation\Contracts\MapperInterface;
use Sif\Foundation\Model\BaseModel;
use Sif\Foundation\Model\Metadata\ModelMetadata;
use Sif\Foundation\Model\State\ModelHydrator;
use Sif\Foundation\Model\State\ModelSerializer;
use Sif\Foundation\Persistence\StorageRecord;

/** @implements MapperInterface<BaseModel> */
final readonly class BaseModelMapper implements MapperInterface
{
    /** @var Closure(ModelMetadata, \Sif\Foundation\Model\State\ModelAttributeState): BaseModel */
    private Closure $factory;

    /**
     * @param callable(ModelMetadata, \Sif\Foundation\Model\State\ModelAttributeState): BaseModel $factory
     */
    public function __construct(
        private ModelMetadata $metadata,
        callable $factory,
        private ModelHydrator $hydrator = new ModelHydrator(),
        private ModelSerializer $serializer = new ModelSerializer(),
    ) {
        $this->factory = Closure::fromCallable($factory);
    }

    public function hydrate(StorageRecord $record): object
    {
        $state = $this->hydrator->hydrate($this->metadata, $record->all());
        $model = ($this->factory)($this->metadata, $state);
        $model->markPersisted();

        return $model;
    }

    public function extract(object $object): StorageRecord
    {
        if (!$object instanceof BaseModel || $object::class !== $this->metadata->modelClass()) {
            throw new \InvalidArgumentException(sprintf(
                'Mapper for "%s" cannot extract object of type "%s".',
                $this->metadata->modelClass(),
                $object::class,
            ));
        }

        return new StorageRecord($this->serializer->storageArray($this->metadata, $object->state()));
    }
}
