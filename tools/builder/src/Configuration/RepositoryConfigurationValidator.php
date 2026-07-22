<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration;

final class RepositoryConfigurationValidator
{
    /** @param array<string, mixed> $data */
    public function validate(array $data, ?string $sourcePath = null): ConfigurationLoadResult
    {
        $diagnostics = [];

        foreach (['schema_version', 'default_profile', 'profiles'] as $field) {
            if (!array_key_exists($field, $data)) {
                $diagnostics[] = new ConfigurationDiagnostic(
                    'CONFIG-104',
                    sprintf('Required configuration field "%s" is missing.', $field),
                    $sourcePath,
                    ['field' => $field],
                );
            }
        }

        if ($diagnostics !== []) {
            return new ConfigurationLoadResult(null, $diagnostics);
        }

        if (!is_string($data['schema_version']) || $data['schema_version'] !== '1.0') {
            return new ConfigurationLoadResult(null, [new ConfigurationDiagnostic(
                'CONFIG-103',
                'Only repository configuration schema version "1.0" is supported.',
                $sourcePath,
            )]);
        }

        if (!is_string($data['default_profile']) || trim($data['default_profile']) === '') {
            $diagnostics[] = $this->invalidValue('default_profile', $sourcePath);
        }

        if (!is_array($data['profiles']) || $data['profiles'] === [] || array_is_list($data['profiles'])) {
            $diagnostics[] = $this->invalidValue('profiles', $sourcePath);
        }

        if ($diagnostics !== []) {
            return new ConfigurationLoadResult(null, $diagnostics);
        }

        /** @var array<string, mixed> $profiles */
        $profiles = $data['profiles'];
        $normalizedProfiles = [];

        foreach ($profiles as $identifier => $profile) {
            if (!$this->validIdentifier($identifier) || !is_array($profile)) {
                $diagnostics[] = $this->invalidValue(sprintf('profiles.%s', $identifier), $sourcePath);
                continue;
            }

            $normalizedProfiles[$identifier] = $profile;
        }

        $defaultProfile = $data['default_profile'];
        if (is_string($defaultProfile) && !array_key_exists($defaultProfile, $normalizedProfiles)) {
            $diagnostics[] = new ConfigurationDiagnostic(
                'CONFIG-106',
                sprintf('Default profile "%s" is not declared.', $defaultProfile),
                $sourcePath,
                ['profile' => $defaultProfile],
            );
        }

        $repositoryPolicies = $data['repository_policies'] ?? [];
        if (!is_array($repositoryPolicies) || ($repositoryPolicies !== [] && array_is_list($repositoryPolicies))) {
            $diagnostics[] = $this->invalidValue('repository_policies', $sourcePath);
            $repositoryPolicies = [];
        }

        if ($diagnostics !== []) {
            return new ConfigurationLoadResult(null, $diagnostics);
        }

        /** @var array<string, list<array<string, mixed>>> $repositoryPolicies */
        return new ConfigurationLoadResult(new RepositoryConfiguration(
            schemaVersion: '1.0',
            defaultProfile: $defaultProfile,
            profiles: $normalizedProfiles,
            repositoryPolicies: $repositoryPolicies,
            sourcePath: $sourcePath,
        ));
    }

    private function invalidValue(string $field, ?string $sourcePath): ConfigurationDiagnostic
    {
        return new ConfigurationDiagnostic(
            'CONFIG-105',
            sprintf('Configuration field "%s" has an invalid type or value.', $field),
            $sourcePath,
            ['field' => $field],
        );
    }

    private function validIdentifier(string $identifier): bool
    {
        return preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) === 1;
    }
}
