<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class SharedSignalsProductReadinessReport
{
    /**
     * @param list<string> $blockingIssues
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $ready,
        private array $blockingIssues = [],
        private array $warnings = []
    ) {
    }

    public function ready(): bool { return $this->ready; }

    /** @return list<string> */
    public function blockingIssues(): array { return $this->blockingIssues; }

    /** @return list<string> */
    public function warnings(): array { return $this->warnings; }
}
