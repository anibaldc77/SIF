<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

final readonly class ValidationResult
{
    /** @var list<ValidationIssue> */
    private array $issues;

    /** @param list<ValidationIssue> $issues */
    public function __construct(array $issues = [])
    {
        usort($issues, static fn (ValidationIssue $a, ValidationIssue $b): int =>
            [$a->path(), $a->code(), $a->message()] <=> [$b->path(), $b->code(), $b->message()]
        );
        $this->issues = array_values($issues);
    }

    public function valid(): bool { return $this->issues === []; }

    /** @return list<ValidationIssue> */
    public function issues(): array { return $this->issues; }

    /** @return list<array{code:string,path:string,message:string,metadata:array<string, scalar|null>}> */
    public function toArray(): array
    {
        return array_map(static fn (ValidationIssue $issue): array => $issue->toArray(), $this->issues);
    }
}
