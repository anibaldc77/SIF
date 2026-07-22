<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy\Factory;

use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredCategoryPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyRuleInterface;
use Sif\Builder\Configuration\Policy\RepositoryPolicyFactoryInterface;

final readonly class RequiredCategoryPolicyFactory implements RepositoryPolicyFactoryInterface
{
    use PolicyFactorySupport;

    public function id(): string
    {
        return 'required.category';
    }

    public function create(array $configuration): RepositoryPolicyRuleInterface
    {
        $this->rejectUnknownKeys($configuration, ['id', 'category', 'severity']);
        return new RequiredCategoryPolicy(
            identifier: $this->requiredString($configuration, 'id'),
            category: $this->requiredString($configuration, 'category'),
            severity: $this->severity($configuration),
        );
    }
}
