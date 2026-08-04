<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Cookie;

use Sif\Foundation\Http\Cookie\Exceptions\InvalidCookieException;

final readonly class Cookie
{
    public function __construct(
        private CookieName $name,
        private CookieValue $value,
        private string $path = '/',
        private ?string $domain = null,
        private bool $secure = false,
        private bool $httpOnly = true,
        private CookieSameSite $sameSite = CookieSameSite::Lax,
        private CookieExpiration $expiration = new CookieExpiration(),
    ) {
        self::assertPath($path);
        self::assertDomain($domain);

        if ($sameSite === CookieSameSite::None && !$secure) {
            throw new InvalidCookieException('SameSite=None cookies must also use Secure.');
        }
        if ($name->hasSecurePrefix() && !$secure) {
            throw new InvalidCookieException('__Secure- cookies must use Secure.');
        }
        if ($name->hasHostPrefix() && (!$secure || $path !== '/' || $domain !== null)) {
            throw new InvalidCookieException('__Host- cookies require Secure, Path=/ and no Domain attribute.');
        }
    }

    public static function create(string $name, string $value): self
    {
        return new self(new CookieName($name), new CookieValue($value));
    }

    public static function removal(
        string $name,
        string $path = '/',
        ?string $domain = null,
        bool $secure = false,
        bool $httpOnly = true,
        CookieSameSite $sameSite = CookieSameSite::Lax,
    ): self {
        return new self(
            new CookieName($name),
            new CookieValue(''),
            $path,
            $domain,
            $secure,
            $httpOnly,
            $sameSite,
            CookieExpiration::removal(),
        );
    }

    public function name(): CookieName { return $this->name; }
    public function value(): CookieValue { return $this->value; }
    public function path(): string { return $this->path; }
    public function domain(): ?string { return $this->domain; }
    public function secure(): bool { return $this->secure; }
    public function httpOnly(): bool { return $this->httpOnly; }
    public function sameSite(): CookieSameSite { return $this->sameSite; }
    public function expiration(): CookieExpiration { return $this->expiration; }

    public function withValue(CookieValue $value): self
    {
        return new self(
            $this->name,
            $value,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httpOnly,
            $this->sameSite,
            $this->expiration,
        );
    }

    private static function assertPath(string $path): void
    {
        if ($path === '' || !str_starts_with($path, '/') || preg_match('/[;\r\n\x00]/', $path) === 1) {
            throw new InvalidCookieException('Cookie Path must be an absolute path without separators or control characters.');
        }
    }

    private static function assertDomain(?string $domain): void
    {
        if ($domain === null) {
            return;
        }
        if (
            $domain === ''
            || $domain !== strtolower($domain)
            || str_contains($domain, '://')
            || preg_match('/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?)(?:\.(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?))*$/', $domain) !== 1
        ) {
            throw new InvalidCookieException('Cookie Domain must be a normalized DNS host name.');
        }
    }
}
