<?php

declare(strict_types=1);

namespace Sif\Builder\Configuration\Policy\Factory;

use Sif\Builder\Analyzer\RepositoryPolicy\Policy\RequiredMetadataPolicy;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyRuleInterface;
use Sif\Builder\Configuration\Policy\RepositoryPolicyFactoryInterface;

final readonly class RequiredMetadataPolicyFactory implements RepositoryPolicyFactoryInterface
{
    use PolicyFactorySupport;

    public function id(): string
    {
        return 'required.metadata';
    }

    public function create(array $configuration): RepositoryPolicyRuleInterface
    {
        $this->rejectUnknownKeys($configuration, ['id', 'field', 'category', 'status', 'severity']);
        return new RequiredMetadataPolicy(
            identifier: $this->requiredString($configuration, 'id'),
            field: $this->requiredString($configuration, 'field'),
            category: $this->optionalString($configuration, 'category'),
            status: $this->optionalString($configuration, 'status'),
            severity: $this->severity($configuration),
        );
    }
}
