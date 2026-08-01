<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Relation;

use Sif\Foundation\Model\Exceptions\InvalidModelRelationDefinitionException;
use Sif\Foundation\Model\Metadata\ModelMetadata;

final readonly class ModelRelationDefinition
{
    /** @var non-empty-list<string> */
    private array $localAttributes;

    /** @var non-empty-list<string> */
    private array $foreignAttributes;

    /**
     * @param list<string> $localAttributes
     * @param list<string> $foreignAttributes
     */
    public function __construct(
        private string $name,
        private ModelMetadata $ownerMetadata,
        private ModelMetadata $relatedMetadata,
        private ModelRelationType $type,
        array $localAttributes,
        array $foreignAttributes,
    ) {
        if (preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name) !== 1) {
            throw new InvalidModelRelationDefinitionException('Relation name is invalid.');
        }

        if (count($localAttributes) !== count($foreignAttributes)) {
            throw new InvalidModelRelationDefinitionException('Relation key cardinality must match.');
        }

        if ($localAttributes === [] || $foreignAttributes === []) {
            throw new InvalidModelRelationDefinitionException('Relation keys cannot be empty.');
        }

        foreach ($localAttributes as $attribute) {
            if (!$ownerMetadata->hasAttribute($attribute)) {
                throw new InvalidModelRelationDefinitionException(sprintf(
                    'Unknown owner relation attribute "%s".',
                    $attribute,
                ));
            }
        }

        foreach ($foreignAttributes as $attribute) {
            if (!$relatedMetadata->hasAttribute($attribute)) {
                throw new InvalidModelRelationDefinitionException(sprintf(
                    'Unknown related relation attribute "%s".',
                    $attribute,
                ));
            }
        }

        /** @var non-empty-list<string> $normalizedLocal */
        $normalizedLocal = array_values($localAttributes);
        /** @var non-empty-list<string> $normalizedForeign */
        $normalizedForeign = array_values($foreignAttributes);
        $this->localAttributes = $normalizedLocal;
        $this->foreignAttributes = $normalizedForeign;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function ownerMetadata(): ModelMetadata
    {
        return $this->ownerMetadata;
    }

    public function relatedMetadata(): ModelMetadata
    {
        return $this->relatedMetadata;
    }

    public function type(): ModelRelationType
    {
        return $this->type;
    }

    /** @return non-empty-list<string> */
    public function localAttributes(): array
    {
        return $this->localAttributes;
    }

    /** @return non-empty-list<string> */
    public function foreignAttributes(): array
    {
        return $this->foreignAttributes;
    }
}
