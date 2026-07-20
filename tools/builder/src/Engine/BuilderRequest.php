<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use Sif\Builder\Engine\Exception\InvalidBuilderRequestException;

final readonly class BuilderRequest
{
    public string $repositoryRoot;

    public string $profile;

    public ?string $outputRoot;

    public ExecutionPolicy $policy;

    /** @var list<string> */
    public array $enabledAnalyzers;

    /** @var list<string> */
    public array $enabledGenerators;

    /**
     * @param list<string> $enabledAnalyzers
     * @param list<string> $enabledGenerators
     */
    public function __construct(
        string $repositoryRoot,
        string $profile = 'default',
        ?string $outputRoot = null,
        ExecutionPolicy $policy = ExecutionPolicy::STRICT,
        array $enabledAnalyzers = [],
        array $enabledGenerators = [],
    ) {
        $this->repositoryRoot = self::normalizePath($repositoryRoot, 'Repository root');
        $this->outputRoot = $outputRoot === null ? null : self::normalizePath($outputRoot, 'Output root');
        $this->profile = self::normalizeIdentifier($profile, 'Execution profile');
        $this->policy = $policy;
        $this->enabledAnalyzers = self::normalizeExtensionIdentifiers($enabledAnalyzers, 'analyzer');
        $this->enabledGenerators = self::normalizeExtensionIdentifiers($enabledGenerators, 'generator');
    }


    private static function normalizePath(string $path, string $label): string
    {
        $path = trim(str_replace('\\', '/', $path));

        if ($path === '') {
            throw new InvalidBuilderRequestException(sprintf('%s must not be empty.', $label));
        }

        if (str_contains($path, "\0")) {
            throw new InvalidBuilderRequestException(sprintf('%s must not contain null bytes.', $label));
        }

        if ($path !== '/' && !preg_match('/^[A-Za-z]:\/$/', $path)) {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    private static function normalizeIdentifier(string $identifier, string $label): string
    {
        $identifier = strtolower(trim($identifier));

        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $identifier)) {
            throw new InvalidBuilderRequestException(sprintf('%s "%s" is invalid.', $label, $identifier));
        }

        return $identifier;
    }

    /**
     * @param list<string> $identifiers
     * @return list<string>
     */
    private static function normalizeExtensionIdentifiers(array $identifiers, string $kind): array
    {
        $normalized = [];

        foreach ($identifiers as $identifier) {
            $identifier = self::normalizeIdentifier($identifier, ucfirst($kind) . ' identifier');

            if (isset($normalized[$identifier])) {
                throw new InvalidBuilderRequestException(sprintf(
                    'Duplicate %s identifier "%s".',
                    $kind,
                    $identifier,
                ));
            }

            $normalized[$identifier] = $identifier;
        }

        return array_values($normalized);
    }
}
