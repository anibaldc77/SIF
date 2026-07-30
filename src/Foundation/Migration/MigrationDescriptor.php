<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationDescriptorException;

final readonly class MigrationDescriptor
{
    private MigrationId $id;

    private ?MigrationVersion $version;

    private MigrationChecksum $checksum;

    /** @var list<MigrationId> */
    private array $dependencies;

    private bool $reversible;

    /** @var list<string> */
    private array $tags;

    private ?string $owner;

    /**
     * @param iterable<MigrationId> $dependencies
     * @param iterable<string> $tags
     */
    public function __construct(
        MigrationId $id,
        MigrationChecksum $checksum,
        ?MigrationVersion $version = null,
        iterable $dependencies = [],
        bool $reversible = false,
        iterable $tags = [],
        ?string $owner = null,
    ) {
        $normalizedDependencies = [];
        $seenDependencies = [];

        foreach ($dependencies as $dependency) {
            if (!$dependency instanceof MigrationId) {
                throw new InvalidMigrationDescriptorException(
                    'Migration dependencies must contain only MigrationId values.',
                );
            }

            if ($dependency->equals($id)) {
                throw new InvalidMigrationDescriptorException('Migration cannot depend on itself.');
            }

            if (isset($seenDependencies[$dependency->value()])) {
                throw new InvalidMigrationDescriptorException(
                    sprintf('Duplicate migration dependency "%s".', $dependency->value()),
                );
            }

            $seenDependencies[$dependency->value()] = true;
            $normalizedDependencies[] = $dependency;
        }

        $normalizedTags = [];
        $seenTags = [];

        foreach ($tags as $tag) {
            if (!is_string($tag)) {
                throw new InvalidMigrationDescriptorException('Migration tags must contain only strings.');
            }

            $tag = strtolower(trim($tag));
            if ($tag === '' || preg_match('/^[a-z0-9][a-z0-9._-]*$/D', $tag) !== 1) {
                throw new InvalidMigrationDescriptorException('Migration tag contains unsafe vocabulary.');
            }

            if (isset($seenTags[$tag])) {
                throw new InvalidMigrationDescriptorException(sprintf('Duplicate migration tag "%s".', $tag));
            }

            $seenTags[$tag] = true;
            $normalizedTags[] = $tag;
        }

        if ($owner !== null) {
            $owner = trim($owner);
            if ($owner === '' || preg_match('/^[A-Za-z0-9][A-Za-z0-9._:-]*$/D', $owner) !== 1) {
                throw new InvalidMigrationDescriptorException('Migration owner must be a safe non-empty token.');
            }
        }

        $this->id = $id;
        $this->checksum = $checksum;
        $this->version = $version;
        $this->dependencies = $normalizedDependencies;
        $this->reversible = $reversible;
        $this->tags = $normalizedTags;
        $this->owner = $owner;
    }

    public function id(): MigrationId
    {
        return $this->id;
    }

    public function checksum(): MigrationChecksum
    {
        return $this->checksum;
    }

    public function version(): ?MigrationVersion
    {
        return $this->version;
    }

    /** @return list<MigrationId> */
    public function dependencies(): array
    {
        return $this->dependencies;
    }

    public function reversible(): bool
    {
        return $this->reversible;
    }

    /** @return list<string> */
    public function tags(): array
    {
        return $this->tags;
    }

    public function owner(): ?string
    {
        return $this->owner;
    }

    /**
     * @return array{
     *   id: string,
     *   version: string|null,
     *   checksum: string,
     *   dependencies: list<string>,
     *   reversible: bool,
     *   tags: list<string>,
     *   owner: string|null
     * }
     */
    public function summary(): array
    {
        return [
            'id' => $this->id->value(),
            'version' => $this->version?->value(),
            'checksum' => $this->checksum->value(),
            'dependencies' => array_map(
                static fn (MigrationId $dependency): string => $dependency->value(),
                $this->dependencies,
            ),
            'reversible' => $this->reversible,
            'tags' => $this->tags,
            'owner' => $this->owner,
        ];
    }
}
