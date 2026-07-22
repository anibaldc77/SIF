<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Profile;

use Sif\Builder\Configuration\ConfigurationDiagnostic;
use Sif\Builder\Configuration\RepositoryConfiguration;

final class BuildProfileResolver implements BuildProfileResolverInterface
{
    public function resolve(
        RepositoryConfiguration $configuration,
        ?string $profileIdentifier = null,
    ): BuildProfileResolutionResult {
        $selectedIdentifier = $profileIdentifier ?? $configuration->defaultProfile;

        if (!array_key_exists($selectedIdentifier, $configuration->profiles)) {
            return new BuildProfileResolutionResult(null, [new ConfigurationDiagnostic(
                'CONFIG-106',
                sprintf('Build profile "%s" is not declared.', $selectedIdentifier),
                $configuration->sourcePath,
                ['profile' => $selectedIdentifier],
            )]);
        }

        $definitions = [];
        foreach ($configuration->profiles as $identifier => $payload) {
            $definition = $this->definition($identifier, $payload, $configuration->sourcePath);
            if ($definition instanceof ConfigurationDiagnostic) {
                return new BuildProfileResolutionResult(null, [$definition]);
            }

            $definitions[$identifier] = $definition;
        }

        $chain = [];
        $current = $selectedIdentifier;
        while (true) {
            if (in_array($current, $chain, true)) {
                $cycle = [...$chain, $current];

                return new BuildProfileResolutionResult(null, [new ConfigurationDiagnostic(
                    'CONFIG-108',
                    sprintf('Build profile inheritance cycle detected: %s.', implode(' -> ', $cycle)),
                    $configuration->sourcePath,
                    ['profile' => $current],
                )]);
            }

            $chain[] = $current;
            $parent = $definitions[$current]->extends;
            if ($parent === null) {
                break;
            }

            if (!array_key_exists($parent, $definitions)) {
                return new BuildProfileResolutionResult(null, [new ConfigurationDiagnostic(
                    'CONFIG-107',
                    sprintf('Parent build profile "%s" declared by "%s" does not exist.', $parent, $current),
                    $configuration->sourcePath,
                    ['profile' => $current, 'parent' => $parent],
                )]);
            }

            $current = $parent;
        }

        $resolved = new ResolvedBuildProfile(
            identifier: $selectedIdentifier,
            analyzers: [],
            generators: [],
            reporters: [],
            strict: false,
        );

        foreach (array_reverse($chain) as $identifier) {
            $definition = $definitions[$identifier];
            $resolved = new ResolvedBuildProfile(
                identifier: $selectedIdentifier,
                analyzers: $definition->analyzers ?? $resolved->analyzers,
                generators: $definition->generators ?? $resolved->generators,
                reporters: $definition->reporters ?? $resolved->reporters,
                strict: $definition->strict ?? $resolved->strict,
            );
        }

        return new BuildProfileResolutionResult($resolved);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function definition(
        string $identifier,
        array $payload,
        ?string $sourcePath,
    ): BuildProfileDefinition|ConfigurationDiagnostic {
        $allowed = ['extends', 'analyzers', 'generators', 'reporters', 'execution'];
        foreach (array_keys($payload) as $field) {
            if (!in_array($field, $allowed, true)) {
                return $this->invalidField($identifier . '.' . $field, $sourcePath);
            }
        }

        $extends = $payload['extends'] ?? null;
        if ($extends !== null && (!is_string($extends) || trim($extends) === '')) {
            return $this->invalidField($identifier . '.extends', $sourcePath);
        }

        $analyzers = $this->identifierList($payload, 'analyzers');
        if ($analyzers instanceof ConfigurationDiagnostic) {
            return $this->invalidField($identifier . '.analyzers', $sourcePath);
        }

        $generators = $this->identifierList($payload, 'generators');
        if ($generators instanceof ConfigurationDiagnostic) {
            return $this->invalidField($identifier . '.generators', $sourcePath);
        }

        $reporters = $this->identifierList($payload, 'reporters');
        if ($reporters instanceof ConfigurationDiagnostic) {
            return $this->invalidField($identifier . '.reporters', $sourcePath);
        }

        $strict = null;
        if (array_key_exists('execution', $payload)) {
            if (!is_array($payload['execution']) || array_is_list($payload['execution'])) {
                return $this->invalidField($identifier . '.execution', $sourcePath);
            }

            foreach (array_keys($payload['execution']) as $field) {
                if ($field !== 'strict') {
                    return $this->invalidField($identifier . '.execution.' . $field, $sourcePath);
                }
            }

            if (array_key_exists('strict', $payload['execution'])) {
                if (!is_bool($payload['execution']['strict'])) {
                    return $this->invalidField($identifier . '.execution.strict', $sourcePath);
                }

                $strict = $payload['execution']['strict'];
            }
        }

        return new BuildProfileDefinition(
            identifier: $identifier,
            extends: $extends,
            analyzers: $analyzers,
            generators: $generators,
            reporters: $reporters,
            strict: $strict,
        );
    }

    /**
     * @param array<string, mixed> $payload
     * @return list<string>|ConfigurationDiagnostic|null
     */
    private function identifierList(array $payload, string $field): array|ConfigurationDiagnostic|null
    {
        if (!array_key_exists($field, $payload)) {
            return null;
        }

        $value = $payload[$field];
        if (!is_array($value) || !array_is_list($value)) {
            return new ConfigurationDiagnostic('CONFIG-105', 'Invalid extension identifier list.');
        }

        $normalized = [];
        foreach ($value as $identifier) {
            if (!is_string($identifier)
                || preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1
                || in_array($identifier, $normalized, true)
            ) {
                return new ConfigurationDiagnostic('CONFIG-105', 'Invalid extension identifier list.');
            }

            $normalized[] = $identifier;
        }

        return $normalized;
    }

    private function invalidField(string $field, ?string $sourcePath): ConfigurationDiagnostic
    {
        return new ConfigurationDiagnostic(
            'CONFIG-105',
            sprintf('Build profile field "%s" has an invalid type or value.', $field),
            $sourcePath,
            ['field' => $field],
        );
    }
}
