<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Parser;

use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Reference\Exception\DuplicateReferenceException;
use Sif\Builder\Reference\Exception\ReferenceParseException;
use Sif\Builder\Reference\Reference;
use Sif\Builder\Reference\ReferenceCollection;
use Sif\Builder\Reference\ReferenceType;

final class FrontMatterReferenceParser implements ReferenceParserInterface
{
    /** @var array<string, ReferenceType> */
    private const FIELD_TYPES = [
        'references' => ReferenceType::REFERENCE,
        'implements' => ReferenceType::IMPLEMENTS,
        'extends' => ReferenceType::EXTENDS,
        'supersedes' => ReferenceType::SUPERSEDES,
        'related' => ReferenceType::RELATED,
        'related_adrs' => ReferenceType::RELATED,
    ];

    public function __construct(
        private readonly ReferenceIdentifierNormalizer $normalizer = new ReferenceIdentifierNormalizer(),
    ) {
    }

    public function parse(MetadataDocument $document): ReferenceCollection
    {
        $references = new ReferenceCollection();
        $sourceIdentifier = $this->normalizeAndValidate(
            $document->id(),
            $document->path,
            'id',
        );

        foreach (self::FIELD_TYPES as $field => $type) {
            if (!array_key_exists($field, $document->metadata)) {
                continue;
            }

            foreach ($this->values($document->metadata[$field], $document->path, $field) as $rawTarget) {
                $targetIdentifier = $this->normalizeAndValidate($rawTarget, $document->path, $field);

                try {
                    $references->add(new Reference(
                        sourceIdentifier: $sourceIdentifier,
                        targetIdentifier: $targetIdentifier,
                        type: $type,
                        context: sprintf('front-matter:%s', $field),
                    ));
                } catch (DuplicateReferenceException) {
                    throw ReferenceParseException::duplicate($document->path, $field, $targetIdentifier);
                }
            }
        }

        return $references;
    }

    /** @return list<string> */
    private function values(mixed $value, string $path, string $field): array
    {
        if ($value === null) {
            return [];
        }

        if (is_string($value)) {
            return [$value];
        }

        if (!is_array($value)) {
            throw ReferenceParseException::invalidFieldValue($path, $field);
        }

        $values = [];
        foreach ($value as $item) {
            if (!is_string($item)) {
                throw ReferenceParseException::invalidFieldValue($path, $field);
            }

            $values[] = $item;
        }

        return $values;
    }

    private function normalizeAndValidate(string $identifier, string $path, string $field): string
    {
        $normalized = $this->normalizer->normalize($identifier);
        if (!$this->normalizer->isValid($normalized)) {
            throw ReferenceParseException::invalidIdentifier($path, $field, $identifier);
        }

        return $normalized;
    }
}
