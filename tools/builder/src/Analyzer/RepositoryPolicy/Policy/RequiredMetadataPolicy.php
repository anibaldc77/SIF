<?php

declare(strict_types=1);

namespace Sif\Builder\Analyzer\RepositoryPolicy\Policy;

use InvalidArgumentException;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyFinding;
use Sif\Builder\Analyzer\RepositoryPolicy\RepositoryPolicyRuleInterface;
use Sif\Builder\Engine\Diagnostic\DiagnosticSeverity;
use Sif\Builder\Metadata\MetadataDocument;
use Sif\Builder\Metadata\MetadataRegistry;

final readonly class RequiredMetadataPolicy implements RepositoryPolicyRuleInterface
{
    public function __construct(
        private string $identifier,
        private string $field,
        private ?string $category = null,
        private ?string $status = null,
        private DiagnosticSeverity $severity = DiagnosticSeverity::ERROR,
    ) {
        if (trim($field) === '') {
            throw new InvalidArgumentException('Required metadata field cannot be empty.');
        }
    }

    public function id(): string
    {
        return $this->identifier;
    }

    public function evaluate(MetadataRegistry $registry): array
    {
        $findings = [];
        foreach ($registry->all() as $document) {
            if (!$this->appliesTo($document)) {
                continue;
            }

            $value = $document->metadata[$this->field] ?? null;
            if ((is_string($value) && trim($value) !== '') || (is_array($value) && $value !== [])) {
                continue;
            }

            $findings[] = new RepositoryPolicyFinding(
                code: 'REPPOL-202',
                severity: $this->severity,
                message: sprintf('Document "%s" violates policy "%s": metadata field "%s" is required.', $document->id(), $this->identifier, $this->field),
                ruleId: $this->identifier,
                sourceIdentifier: $document->id(),
                sourcePath: $document->path,
                context: ['field' => $this->field, 'category' => $this->category, 'status' => $this->status],
                remediation: sprintf('Declare a non-empty "%s" value in document metadata.', $this->field),
            );
        }

        return $findings;
    }

    private function appliesTo(MetadataDocument $document): bool
    {
        if ($this->category !== null && ($document->metadata['category'] ?? null) !== $this->category) {
            return false;
        }

        return $this->status === null || ($document->metadata['status'] ?? null) === $this->status;
    }
}
