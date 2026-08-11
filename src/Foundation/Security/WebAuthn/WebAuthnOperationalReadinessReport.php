<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnOperationalReadinessReport
{
    /**
     * @param list<string> $blockingIssues
     * @param list<string> $advisories
     */
    public function __construct(
        private bool $ready,
        private array $blockingIssues = [],
        private array $advisories = []
    ) {
    }

    public function ready(): bool { return $this->ready; }
    /** @return list<string> */
    public function blockingIssues(): array { return $this->blockingIssues; }
    /** @return list<string> */
    public function advisories(): array { return $this->advisories; }
}
