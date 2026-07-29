<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourceDescriptorException;

final readonly class ResourceDescriptor
{
    private ?string $logicalVersion;
    private ?string $owner;

    /** @var array<string, null|bool|int|float|string> */
    private array $metadata;

    /**
     * @param array<mixed, mixed> $metadata
     */
    public function __construct(
        private ResourceIdentifier $identifier,
        private ResourceNamespace $namespace,
        private ResourceType $type,
        private ResourcePath $source,
        private ResourcePriority $priority = new ResourcePriority(),
        ?string $logicalVersion = null,
        ?string $owner = null,
        array $metadata = [],
    ) {
        $logicalVersion = $this->normalizeOptionalText($logicalVersion, 'logical version');
        $owner = $this->normalizeOptionalText($owner, 'owner');

        /** @var array<string, null|bool|int|float|string> $validatedMetadata */
        $validatedMetadata = [];
        foreach ($metadata as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidResourceDescriptorException('Resource metadata keys must be non-empty strings.');
            }
            if (!is_null($value) && !is_bool($value) && !is_int($value) && !is_float($value) && !is_string($value)) {
                throw new InvalidResourceDescriptorException('Resource metadata values must be scalar or null.');
            }
            $validatedMetadata[$key] = $value;
        }

        $this->logicalVersion = $logicalVersion;
        $this->owner = $owner;
        $this->metadata = $validatedMetadata;
    }

    public function identifier(): ResourceIdentifier { return $this->identifier; }
    public function namespace(): ResourceNamespace { return $this->namespace; }
    public function type(): ResourceType { return $this->type; }
    public function source(): ResourcePath { return $this->source; }
    public function priority(): ResourcePriority { return $this->priority; }
    public function logicalVersion(): ?string { return $this->logicalVersion; }
    public function owner(): ?string { return $this->owner; }

    /** @return array<string, null|bool|int|float|string> */
    public function metadata(): array { return $this->metadata; }

    public function qualifiedIdentifier(): string
    {
        return $this->namespace->value() . ':' . $this->identifier->value();
    }

    /** @return array{identifier:string,namespace:string,type:string,source:string,priority:int,logical_version:?string,owner:?string,metadata:array<string, null|bool|int|float|string>} */
    public function summary(): array
    {
        return [
            'identifier' => $this->identifier->value(),
            'namespace' => $this->namespace->value(),
            'type' => $this->type->value(),
            'source' => $this->source->value(),
            'priority' => $this->priority->value(),
            'logical_version' => $this->logicalVersion,
            'owner' => $this->owner,
            'metadata' => $this->metadata,
        ];
    }

    private function normalizeOptionalText(?string $value, string $field): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strlen($value) > 255 || str_contains($value, "\0")) {
            throw new InvalidResourceDescriptorException(sprintf('Resource %s must be a non-empty bounded string.', $field));
        }

        return $value;
    }
}
