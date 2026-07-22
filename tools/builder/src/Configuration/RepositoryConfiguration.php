<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

use InvalidArgumentException;

final readonly class RepositoryConfiguration
{
    /**
     * @param array<string, array<string, mixed>> $profiles
     * @param array<string, list<array<string, mixed>>> $repositoryPolicies
     */
    public function __construct(
        public string $schemaVersion,
        public string $defaultProfile,
        public array $profiles,
        public array $repositoryPolicies = [],
        public ?string $sourcePath = null,
    ) {
        if ($schemaVersion !== '1.0') {
            throw new InvalidArgumentException(sprintf('Unsupported repository configuration schema "%s".', $schemaVersion));
        }

        if (trim($defaultProfile) === '') {
            throw new InvalidArgumentException('Default profile cannot be empty.');
        }

        if (!array_key_exists($defaultProfile, $profiles)) {
            throw new InvalidArgumentException(sprintf('Default profile "%s" is not declared.', $defaultProfile));
        }
    }

    public static function builtInDefault(): self
    {
        return new self(
            schemaVersion: '1.0',
            defaultProfile: 'default',
            profiles: [
                'default' => [
                    'analyzers' => [
                        'metadata.completeness',
                        'reference.integrity',
                        'document.consistency',
                        'repository.policy',
                        'generated.artifacts',
                    ],
                    'generators' => [
                        'repository.index',
                        'reference.report',
                        'reference.graph',
                        'repository.manifest',
                        'documentation.navigation',
                    ],
                    'reporters' => ['report.markdown', 'report.json'],
                    'execution' => ['strict' => false],
                ],
            ],
        );
    }
}
