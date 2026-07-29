<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Publication;

use JsonException;
use Sif\Foundation\Resources\Exceptions\InvalidResourceManifestException;
use Sif\Foundation\Resources\ResourcePath;

final readonly class ImmutableResourceManifest
{
    /** @var list<ResourceManifestEntry> */
    private array $entries;

    /** @var array<string, ResourceManifestEntry> */
    private array $entriesByTarget;

    private string $canonicalJson;
    private string $fingerprint;

    /** @param list<ResourceManifestEntry> $entries */
    public function __construct(array $entries)
    {
        usort($entries, static function (ResourceManifestEntry $left, ResourceManifestEntry $right): int {
            $targetComparison = strcmp(
                $left->publication()->request()->targetPath()->value(),
                $right->publication()->request()->targetPath()->value(),
            );

            if ($targetComparison !== 0) {
                return $targetComparison;
            }

            return strcmp(
                $left->publication()->request()->qualifiedIdentifier(),
                $right->publication()->request()->qualifiedIdentifier(),
            );
        });

        $byTarget = [];
        foreach ($entries as $entry) {
            $targetKey = self::portableTargetKey($entry->publication()->request()->targetPath());
            if (isset($byTarget[$targetKey])) {
                throw new InvalidResourceManifestException(sprintf('Duplicate manifest target path "%s".', $entry->publication()->request()->targetPath()->value()));
            }
            $byTarget[$targetKey] = $entry;
        }

        try {
            $canonicalJson = json_encode(
                array_map(static fn (ResourceManifestEntry $entry): array => $entry->canonicalData(), $entries),
                JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
            );
        } catch (JsonException $exception) {
            throw new InvalidResourceManifestException('The resource manifest could not be serialized.', previous: $exception);
        }

        $this->entries = array_values($entries);
        $this->entriesByTarget = $byTarget;
        $this->canonicalJson = $canonicalJson;
        $this->fingerprint = hash('sha256', $canonicalJson);
    }

    /** @return list<ResourceManifestEntry> */
    public function entries(): array { return $this->entries; }
    public function count(): int { return count($this->entries); }
    public function canonicalJson(): string { return $this->canonicalJson; }
    public function fingerprint(): string { return $this->fingerprint; }

    public function hasTarget(ResourcePath $targetPath): bool
    {
        return isset($this->entriesByTarget[self::portableTargetKey($targetPath)]);
    }

    public function entryForTarget(ResourcePath $targetPath): ?ResourceManifestEntry
    {
        return $this->entriesByTarget[self::portableTargetKey($targetPath)] ?? null;
    }

    private static function portableTargetKey(ResourcePath $targetPath): string
    {
        return strtolower($targetPath->value());
    }
}
