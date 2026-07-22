<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy;

use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyRuleInterface;

interface RepositoryPolicyFactoryInterface
{
    public function id(): string;

    /** @param array<string, mixed> $configuration */
    public function create(array $configuration): RepositoryPolicyRuleInterface;
}
