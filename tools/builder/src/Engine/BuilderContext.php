<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

use Sif\Builder\Engine\Exception\InvalidBuilderContextException;
use Sif\Builder\Repository\RepositoryIndex;

final readonly class BuilderContext
{
    private ?RepositoryIndex $repositoryIndex;

    /** @var array<string, bool|float|int|string|null> */
    public array $configuration;

    /**
     * @param array<string, bool|float|int|string|null> $configuration
     */
    public function __construct(
        public string $runIdentifier,
        public string $repositoryRoot,
        public string $profile,
        public BuilderPhase $phase = BuilderPhase::CREATED,
        ?RepositoryIndex $repositoryIndex = null,
        array $configuration = [],
    ) {
        if (trim($this->runIdentifier) === '') {
            throw new InvalidBuilderContextException('Run identifier must not be empty.');
        }

        if (trim($this->repositoryRoot) === '') {
            throw new InvalidBuilderContextException('Repository root must not be empty.');
        }

        if (trim($this->profile) === '') {
            throw new InvalidBuilderContextException('Execution profile must not be empty.');
        }

        foreach ($configuration as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidBuilderContextException('Configuration keys must be non-empty strings.');
            }

            if (!is_bool($value) && !is_float($value) && !is_int($value) && !is_string($value) && $value !== null) {
                throw new InvalidBuilderContextException(sprintf(
                    'Configuration value for "%s" must be scalar or null.',
                    $key,
                ));
            }
        }

        ksort($configuration);
        $this->configuration = $configuration;
        $this->repositoryIndex = $repositoryIndex === null ? null : clone $repositoryIndex;
    }

    public static function fromRequest(string $runIdentifier, BuilderRequest $request): self
    {
        return new self(
            runIdentifier: $runIdentifier,
            repositoryRoot: $request->repositoryRoot,
            profile: $request->profile,
        );
    }

    public function repositoryIndex(): ?RepositoryIndex
    {
        return $this->repositoryIndex === null ? null : clone $this->repositoryIndex;
    }

    public function withPhase(BuilderPhase $phase): self
    {
        return new self(
            runIdentifier: $this->runIdentifier,
            repositoryRoot: $this->repositoryRoot,
            profile: $this->profile,
            phase: $phase,
            repositoryIndex: $this->repositoryIndex,
            configuration: $this->configuration,
        );
    }

    public function withRepositoryIndex(RepositoryIndex $repositoryIndex): self
    {
        return new self(
            runIdentifier: $this->runIdentifier,
            repositoryRoot: $this->repositoryRoot,
            profile: $this->profile,
            phase: $this->phase,
            repositoryIndex: $repositoryIndex,
            configuration: $this->configuration,
        );
    }

    /** @param array<string, bool|float|int|string|null> $configuration */
    public function withConfiguration(array $configuration): self
    {
        return new self(
            runIdentifier: $this->runIdentifier,
            repositoryRoot: $this->repositoryRoot,
            profile: $this->profile,
            phase: $this->phase,
            repositoryIndex: $this->repositoryIndex,
            configuration: $configuration,
        );
    }
}
