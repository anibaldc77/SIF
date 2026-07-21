<?php

declare(strict_types=1);

namespace Sif\Builder\Generator\RepositoryIndex;

use Sif\Builder\Engine\Repository\RepositoryWorkspace;
use Sif\Builder\Repository\RepositoryIndexEntry;

final class RepositoryIndexViewFactory
{
    private const ARTIFACT_PATH = 'engineering/INDEX.generated.md';

    /** @var array<string, int> */
    private const TYPE_RANK = [
        'ADR' => 10,
        'RFC' => 20,
        'SPEC' => 30,
        'WP' => 40,
        'GUIDE' => 50,
        'REPORT' => 60,
    ];

    public function create(RepositoryWorkspace $workspace): RepositoryIndexView
    {
        $index = $workspace->repositoryIndex();
        if ($index === null) {
            return new RepositoryIndexView(0, 0, 0, [], [], []);
        }

        /** @var array<string, list<RepositoryIndexEntryView>> $grouped */
        $grouped = [];
        $byStatus = [];
        $byType = [];

        foreach ($index->all() as $entry) {
            $type = $this->documentType($entry);
            $status = $this->displayValue($entry->status);

            $byStatus[$status] = ($byStatus[$status] ?? 0) + 1;
            $byType[$type] = ($byType[$type] ?? 0) + 1;
            $grouped[$type][] = new RepositoryIndexEntryView(
                identifier: trim($entry->identifier),
                title: $this->displayValue($entry->title),
                documentType: $type,
                status: $status,
                version: $this->displayValue($entry->version),
                path: $this->normalizePath($entry->path),
                link: $this->relativeLink(self::ARTIFACT_PATH, $entry->path),
            );
        }

        uksort($grouped, fn (string $left, string $right): int => $this->compareTypes($left, $right));

        $sections = [];
        foreach ($grouped as $type => $entries) {
            usort($entries, static function (RepositoryIndexEntryView $left, RepositoryIndexEntryView $right): int {
                $identifier = strnatcasecmp($left->identifier, $right->identifier);
                return $identifier !== 0 ? $identifier : strnatcasecmp($left->path, $right->path);
            });
            $sections[] = new RepositoryIndexSection($type, $entries);
        }

        $resolution = $workspace->resolution();

        return new RepositoryIndexView(
            totalDocuments: $index->count(),
            resolvedReferences: $resolution?->resolvedCount() ?? 0,
            unresolvedReferences: $resolution?->brokenCount() ?? 0,
            sections: $sections,
            byStatus: $byStatus,
            byType: $byType,
        );
    }

    private function documentType(RepositoryIndexEntry $entry): string
    {
        $class = strtoupper(trim($entry->documentClass));
        if ($class !== '') {
            return $class;
        }

        $identifier = strtoupper(trim($entry->identifier));
        foreach (array_keys(self::TYPE_RANK) as $prefix) {
            if (str_starts_with($identifier, $prefix . '-')) {
                return $prefix;
            }
        }

        return 'UNSPECIFIED';
    }

    private function compareTypes(string $left, string $right): int
    {
        $rank = (self::TYPE_RANK[$left] ?? 900) <=> (self::TYPE_RANK[$right] ?? 900);
        return $rank !== 0 ? $rank : strnatcasecmp($left, $right);
    }

    private function displayValue(string $value): string
    {
        $value = trim($value);
        return $value === '' ? 'Unspecified' : $value;
    }

    private function normalizePath(string $path): string
    {
        return ltrim(str_replace('\\', '/', trim($path)), '/');
    }

    private function relativeLink(string $fromArtifact, string $target): string
    {
        $from = explode('/', dirname(str_replace('\\', '/', $fromArtifact)));
        $to = explode('/', $this->normalizePath($target));

        while ($from !== [] && $to !== [] && strcasecmp($from[0], $to[0]) === 0) {
            array_shift($from);
            array_shift($to);
        }

        $relative = implode('/', array_merge(array_fill(0, count($from), '..'), $to));
        if ($relative === '') {
            $relative = basename($target);
        }

        return str_replace(
            ['%', ' ', '#', '(', ')'],
            ['%25', '%20', '%23', '%28', '%29'],
            $relative,
        );
    }
}
