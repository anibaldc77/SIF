<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\GeneratedArtifacts;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Metadata\MetadataRegistry;

final readonly class GeneratedArtifactsInspector
{
    /** @return list<GeneratedArtifactsFinding> */
    public function inspect(
        string $repositoryRoot,
        string $artifactRoot,
        MetadataRegistry $registry,
        GeneratedArtifactCatalog $catalog,
    ): array {
        $repositoryRoot = rtrim($repositoryRoot, '/\\');
        $artifactRoot = rtrim($artifactRoot, '/\\');
        $latestSourceModification = $this->latestSourceModification($repositoryRoot, $registry);
        $findings = [];

        foreach ($catalog->all() as $definition) {
            $absolutePath = $artifactRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $definition->relativePath);
            if (!file_exists($absolutePath)) {
                $findings[] = new GeneratedArtifactsFinding(
                    code: 'GENART-201',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Governed artifact "%s" is missing.', $definition->relativePath),
                    sourcePath: $definition->relativePath,
                    context: ['generator' => $definition->generator],
                    remediation: 'Run the corresponding generator to create the governed artifact.',
                );
                continue;
            }

            if (!is_file($absolutePath)) {
                $findings[] = new GeneratedArtifactsFinding(
                    code: 'GENART-205',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Governed artifact path "%s" is not a regular file.', $definition->relativePath),
                    sourcePath: $definition->relativePath,
                    context: ['generator' => $definition->generator],
                    remediation: 'Remove the conflicting filesystem entry and regenerate the artifact.',
                );
                continue;
            }

            if (filesize($absolutePath) === 0) {
                $findings[] = new GeneratedArtifactsFinding(
                    code: 'GENART-202',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Governed artifact "%s" is empty.', $definition->relativePath),
                    sourcePath: $definition->relativePath,
                    context: ['generator' => $definition->generator],
                    remediation: 'Regenerate the artifact and verify the generator output.',
                );
            }

            $artifactModification = filemtime($absolutePath);
            if (
                $latestSourceModification !== null
                && $artifactModification !== false
                && $artifactModification < $latestSourceModification
            ) {
                $findings[] = new GeneratedArtifactsFinding(
                    code: 'GENART-203',
                    severity: DiagnosticSeverity::WARNING,
                    message: sprintf('Governed artifact "%s" is older than repository source metadata.', $definition->relativePath),
                    sourcePath: $definition->relativePath,
                    context: [
                        'generator' => $definition->generator,
                        'artifact_modified_at' => $artifactModification,
                        'latest_source_modified_at' => $latestSourceModification,
                    ],
                    remediation: 'Regenerate the artifact from the current repository metadata.',
                );
            }
        }

        foreach ($this->unexpectedGeneratedPaths($artifactRoot, $catalog) as $relativePath) {
            $findings[] = new GeneratedArtifactsFinding(
                code: 'GENART-204',
                severity: DiagnosticSeverity::WARNING,
                message: sprintf('Generated artifact "%s" is not registered in the governed catalog.', $relativePath),
                sourcePath: $relativePath,
                remediation: 'Register the artifact explicitly or remove the obsolete generated file.',
            );
        }

        usort($findings, static fn (GeneratedArtifactsFinding $left, GeneratedArtifactsFinding $right): int => $left->identity() <=> $right->identity());

        return $findings;
    }

    private function latestSourceModification(string $repositoryRoot, MetadataRegistry $registry): ?int
    {
        $latest = null;
        foreach ($registry->all() as $document) {
            $path = $repositoryRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $document->path);
            $modified = is_file($path) ? filemtime($path) : false;
            if ($modified !== false && ($latest === null || $modified > $latest)) {
                $latest = $modified;
            }
        }

        return $latest;
    }

    /** @return list<string> */
    private function unexpectedGeneratedPaths(string $artifactRoot, GeneratedArtifactCatalog $catalog): array
    {
        if (!is_dir($artifactRoot)) {
            return [];
        }

        $expected = array_fill_keys($catalog->paths(), true);
        $unexpected = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($artifactRoot, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($iterator as $entry) {
            if (!$entry->isFile()) {
                continue;
            }
            $absolutePath = str_replace('\\', '/', $entry->getPathname());
            $root = str_replace('\\', '/', $artifactRoot);
            $relativePath = ltrim(substr($absolutePath, strlen(rtrim($root, '/'))), '/');
            if (!preg_match('/\.generated\.(?:json|md)$/', $relativePath)) {
                continue;
            }
            if (!isset($expected[$relativePath])) {
                $unexpected[] = $relativePath;
            }
        }

        sort($unexpected);

        return $unexpected;
    }
}
