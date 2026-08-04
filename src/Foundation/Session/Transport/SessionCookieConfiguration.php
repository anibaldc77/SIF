<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Transport;

use Sif\Foundation\Http\Cookie\CookieSameSite;

final readonly class SessionCookieConfiguration
{
    public function __construct(
        private string $name = '__Host-sif_session',
        private string $path = '/',
        private ?string $domain = null,
        private bool $secure = true,
        private bool $httpOnly = true,
        private CookieSameSite $sameSite = CookieSameSite::Lax,
        private ?int $maxAge = null,
    ) {
    }

    public function name(): string { return $this->name; }
    public function path(): string { return $this->path; }
    public function domain(): ?string { return $this->domain; }
    public function secure(): bool { return $this->secure; }
    public function httpOnly(): bool { return $this->httpOnly; }
    public function sameSite(): CookieSameSite { return $this->sameSite; }
    public function maxAge(): ?int { return $this->maxAge; }
}
