<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Advanced;

final readonly class OAuthAdvancedSecurityReadinessReport
{
    /** @param list<string> $missingCapabilities */
    public function __construct(
        private bool $ready,
        private array $missingCapabilities = []
    ) {
    }

    public function ready(): bool { return $this->ready; }

    /** @return list<string> */
    public function missingCapabilities(): array { return $this->missingCapabilities; }
}
