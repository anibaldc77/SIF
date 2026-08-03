<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

final readonly class ActionArgumentResolution
{
    /**
     * @param list<mixed> $arguments
     * @param array<string, mixed> $namedArguments
     * @param list<ActionArgumentIssue> $issues
     */
    public function __construct(
        private array $arguments,
        private array $namedArguments,
        private array $issues,
    ) {
    }

    /** @return list<mixed> */
    public function arguments(): array { return $this->arguments; }
    /** @return array<string, mixed> */
    public function namedArguments(): array { return $this->namedArguments; }
    /** @return list<ActionArgumentIssue> */
    public function issues(): array { return $this->issues; }
    public function successful(): bool { return $this->issues === []; }
}
