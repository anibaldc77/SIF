<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

final readonly class RouteUrlGenerationResult
{
    /** @param list<string> $issues */
    private function __construct(private ?string $url, private array $issues)
    {
    }

    public static function generated(string $url): self { return new self($url, []); }
    /** @param list<string> $issues */ public static function failed(array $issues): self { return new self(null, $issues); }
    public function successful(): bool { return $this->url !== null; }
    public function url(): ?string { return $this->url; }
    /** @return list<string> */ public function issues(): array { return $this->issues; }
}
