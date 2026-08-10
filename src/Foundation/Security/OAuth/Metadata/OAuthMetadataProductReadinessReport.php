<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\OAuth\Metadata;
final readonly class OAuthMetadataProductReadinessReport
{
    /**
     * @param list<string> $missingCapabilities
     * @param list<string> $warnings
     */
    public function __construct(private bool $ready, private array $missingCapabilities=[], private array $warnings=[]) {}
    public function ready(): bool { return $this->ready; }
    /** @return list<string> */ public function missingCapabilities(): array { return $this->missingCapabilities; }
    /** @return list<string> */ public function warnings(): array { return $this->warnings; }
}
