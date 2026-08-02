<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Manifest;

use Sif\Foundation\ApplicationSkeleton\Exceptions\InvalidProjectManifestException;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectIdentifier;
use Sif\Foundation\ApplicationSkeleton\Value\ProjectNamespace;

final readonly class ProjectManifest
{
    /** @var array<string, ProjectEntryPoint> */
    private array $entryPoints;

    /** @var list<string> */
    private array $environments;

    /** @var array<string, ProjectPathDefinition> */
    private array $paths;

    /** @var list<string> */
    private array $capabilities;

    /**
     * @param iterable<ProjectEntryPoint> $entryPoints
     * @param iterable<string> $environments
     * @param iterable<ProjectPathDefinition> $paths
     * @param iterable<string> $capabilities
     */
    public function __construct(
        private ProjectIdentifier $identifier,
        private string $name,
        private ProjectNamespace $namespace,
        private string $schemaVersion,
        private string $skeletonVersion,
        private string $sifConstraint,
        private string $minimumPhpVersion,
        iterable $entryPoints,
        iterable $environments,
        iterable $paths,
        iterable $capabilities = [],
    ) {
        if (trim($name) === '' || preg_match('/[\x00-\x1F\x7F]/', $name) === 1) {
            throw new InvalidProjectManifestException('Project name cannot be empty or contain control characters.');
        }

        foreach ([$schemaVersion, $skeletonVersion, $minimumPhpVersion] as $version) {
            if (preg_match('/^\d+\.\d+\.\d+(?:-[0-9A-Za-z.-]+)?(?:\+[0-9A-Za-z.-]+)?$/', $version) !== 1) {
                throw new InvalidProjectManifestException(
                    sprintf('Version "%s" must use Semantic Versioning.', $version),
                );
            }
        }

        if (trim($sifConstraint) === '' || preg_match('/[\x00-\x1F\x7F]/', $sifConstraint) === 1) {
            throw new InvalidProjectManifestException('The SIF version constraint is invalid.');
        }

        $normalizedEntryPoints = [];
        foreach ($entryPoints as $entryPoint) {
            if (isset($normalizedEntryPoints[$entryPoint->name()])) {
                throw new InvalidProjectManifestException(
                    sprintf('Duplicate entry point "%s".', $entryPoint->name()),
                );
            }
            $normalizedEntryPoints[$entryPoint->name()] = $entryPoint;
        }
        if ($normalizedEntryPoints === []) {
            throw new InvalidProjectManifestException('At least one entry point is required.');
        }
        ksort($normalizedEntryPoints);
        $this->entryPoints = $normalizedEntryPoints;

        $normalizedEnvironments = [];
        foreach ($environments as $environment) {
            if (preg_match('/^[a-z][a-z0-9-]*$/', $environment) !== 1) {
                throw new InvalidProjectManifestException(
                    sprintf('Invalid environment "%s".', $environment),
                );
            }
            $normalizedEnvironments[$environment] = true;
        }
        if ($normalizedEnvironments === []) {
            throw new InvalidProjectManifestException('At least one environment is required.');
        }
        $environmentNames = array_keys($normalizedEnvironments);
        sort($environmentNames);
        $this->environments = $environmentNames;

        $normalizedPaths = [];
        foreach ($paths as $path) {
            $key = $path->path()->value();
            if (isset($normalizedPaths[$key])) {
                throw new InvalidProjectManifestException(sprintf('Duplicate path "%s".', $key));
            }
            $normalizedPaths[$key] = $path;
        }
        if ($normalizedPaths === []) {
            throw new InvalidProjectManifestException('At least one project path is required.');
        }
        ksort($normalizedPaths);
        $this->paths = $normalizedPaths;

        $normalizedCapabilities = [];
        foreach ($capabilities as $capability) {
            if (preg_match('/^[a-z][a-z0-9]*(?:[.-][a-z0-9]+)*$/', $capability) !== 1) {
                throw new InvalidProjectManifestException(
                    sprintf('Invalid capability "%s".', $capability),
                );
            }
            $normalizedCapabilities[$capability] = true;
        }
        $capabilityNames = array_keys($normalizedCapabilities);
        sort($capabilityNames);
        $this->capabilities = $capabilityNames;
    }

    public function identifier(): ProjectIdentifier { return $this->identifier; }
    public function name(): string { return $this->name; }
    public function namespace(): ProjectNamespace { return $this->namespace; }
    public function schemaVersion(): string { return $this->schemaVersion; }
    public function skeletonVersion(): string { return $this->skeletonVersion; }
    public function sifConstraint(): string { return $this->sifConstraint; }
    public function minimumPhpVersion(): string { return $this->minimumPhpVersion; }

    /** @return array<string, ProjectEntryPoint> */
    public function entryPoints(): array { return $this->entryPoints; }

    /** @return list<string> */
    public function environments(): array { return $this->environments; }

    /** @return array<string, ProjectPathDefinition> */
    public function paths(): array { return $this->paths; }

    /** @return list<string> */
    public function capabilities(): array { return $this->capabilities; }

    /**
     * @return array{
     *   schema_version: string,
     *   project: array{id: string, name: string, namespace: string},
     *   skeleton_version: string,
     *   sif: string,
     *   php: string,
     *   entry_points: list<array{name: string, path: string}>,
     *   environments: list<string>,
     *   paths: list<array{path: string, ownership: string, overwrite_policy: string}>,
     *   capabilities: list<string>
     * }
     */
    public function toArray(): array
    {
        return [
            'schema_version' => $this->schemaVersion,
            'project' => [
                'id' => $this->identifier->value(),
                'name' => $this->name,
                'namespace' => $this->namespace->value(),
            ],
            'skeleton_version' => $this->skeletonVersion,
            'sif' => $this->sifConstraint,
            'php' => $this->minimumPhpVersion,
            'entry_points' => array_values(array_map(
                static fn (ProjectEntryPoint $entryPoint): array => $entryPoint->toArray(),
                $this->entryPoints,
            )),
            'environments' => $this->environments,
            'paths' => array_values(array_map(
                static fn (ProjectPathDefinition $path): array => $path->toArray(),
                $this->paths,
            )),
            'capabilities' => $this->capabilities,
        ];
    }
}
