<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Schema;

use Sif\Foundation\Configuration\ImmutableConfigurationRepository;

final readonly class ConfigurationValidationResult
{
    /** @param list<ConfigurationValidationIssue> $issues */
    public function __construct(
        private ImmutableConfigurationRepository $repository,
        private array $issues = [],
    ) {
    }

    public function isValid(): bool
    {
        return $this->issues === [];
    }

    public function repository(): ImmutableConfigurationRepository
    {
        return $this->repository;
    }

    /** @return list<ConfigurationValidationIssue> */
    public function issues(): array
    {
        return $this->issues;
    }
}
