<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

use JsonException;

final readonly class JsonRepositoryConfigurationLoader implements RepositoryConfigurationLoaderInterface
{
    public function __construct(
        private RepositoryConfigurationValidator $validator = new RepositoryConfigurationValidator(),
    ) {
    }

    public function load(string $repositoryRoot, ?string $configurationPath = null): ConfigurationLoadResult
    {
        $path = $configurationPath ?? rtrim($repositoryRoot, '/\\') . DIRECTORY_SEPARATOR . '.sif' . DIRECTORY_SEPARATOR . 'builder.json';

        if (!file_exists($path)) {
            return new ConfigurationLoadResult(RepositoryConfiguration::builtInDefault());
        }

        if (!is_file($path) || !is_readable($path)) {
            return new ConfigurationLoadResult(null, [new ConfigurationDiagnostic(
                'CONFIG-101',
                sprintf('Repository configuration "%s" cannot be read.', $path),
                $path,
            )]);
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            return new ConfigurationLoadResult(null, [new ConfigurationDiagnostic(
                'CONFIG-101',
                sprintf('Repository configuration "%s" cannot be read.', $path),
                $path,
            )]);
        }

        try {
            $decoded = json_decode($contents, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            return new ConfigurationLoadResult(null, [new ConfigurationDiagnostic(
                'CONFIG-102',
                sprintf('Repository configuration contains invalid JSON: %s', $exception->getMessage()),
                $path,
            )]);
        }

        if (!is_array($decoded) || array_is_list($decoded)) {
            return new ConfigurationLoadResult(null, [new ConfigurationDiagnostic(
                'CONFIG-105',
                'Repository configuration root must be a JSON object.',
                $path,
            )]);
        }

        /** @var array<string, mixed> $decoded */
        return $this->validator->validate($decoded, $path);
    }
}
