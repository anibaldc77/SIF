<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy\Policy;

use InvalidArgumentException;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyFinding;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyRuleInterface;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Metadata\MetadataRegistry;

final readonly class RequiredCategoryPolicy implements RepositoryPolicyRuleInterface
{
    public function __construct(
        private string $identifier,
        private string $category,
        private DiagnosticSeverity $severity = DiagnosticSeverity::ERROR,
    ) {
        if (trim($category) === '') {
            throw new InvalidArgumentException('Required repository category cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->identifier;
    }

    public function evaluate(MetadataRegistry $registry): array
    {
        foreach ($registry->all() as $document) {
            if (($document->metadata['category'] ?? null) === $this->category) {
                return [];
            }
        }

        return [new RepositoryPolicyFinding(
            code: 'REPPOL-201',
            severity: $this->severity,
            message: sprintf('Repository does not contain a document in required category "%s".', $this->category),
            ruleId: $this->identifier,
            context: ['category' => $this->category],
            remediation: sprintf('Add and govern at least one document in category "%s".', $this->category),
        )];
    }
}
