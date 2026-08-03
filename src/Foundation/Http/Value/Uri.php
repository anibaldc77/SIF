<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Value;

use Sif\Foundation\Contracts\UriInterface;
use Sif\Foundation\Http\Exceptions\InvalidHttpUriException;

final readonly class Uri implements UriInterface
{
    public function __construct(
        private string $scheme = '',
        private string $userInfo = '',
        private string $host = '',
        private ?int $port = null,
        private string $path = '',
        private string $query = '',
        private string $fragment = '',
    ) {
        self::assertScheme($scheme);
        self::assertHost($host);
        self::assertPort($port);
        self::assertComponent($userInfo, 'user info');
        self::assertPath($path);
        self::assertComponent($query, 'query');
        self::assertComponent($fragment, 'fragment');

        if ($host === '' && ($userInfo !== '' || $port !== null)) {
            throw new InvalidHttpUriException('URI user info and port require a host.');
        }
    }

    public static function fromString(string $uri): self
    {
        if ($uri === '' || preg_match('/[\x00-\x20\x7F]/', $uri) === 1) {
            throw new InvalidHttpUriException('URI must be non-empty and contain no control characters or spaces.');
        }

        $parts = parse_url($uri);
        if ($parts === false) {
            throw new InvalidHttpUriException(sprintf('Invalid URI "%s".', $uri));
        }

        $userInfo = isset($parts['user']) ? (string) $parts['user'] : '';
        if (isset($parts['pass'])) {
            $userInfo .= ':' . (string) $parts['pass'];
        }

        return new self(
            isset($parts['scheme']) ? strtolower((string) $parts['scheme']) : '',
            $userInfo,
            isset($parts['host']) ? strtolower((string) $parts['host']) : '',
            isset($parts['port']) ? (int) $parts['port'] : null,
            isset($parts['path']) ? (string) $parts['path'] : '',
            isset($parts['query']) ? (string) $parts['query'] : '',
            isset($parts['fragment']) ? (string) $parts['fragment'] : '',
        );
    }

    public function scheme(): string { return $this->scheme; }
    public function userInfo(): string { return $this->userInfo; }
    public function host(): string { return $this->host; }
    public function port(): ?int { return $this->port; }
    public function path(): string { return $this->path; }
    public function query(): string { return $this->query; }
    public function fragment(): string { return $this->fragment; }

    public function authority(): string
    {
        if ($this->host === '') {
            return '';
        }

        $authority = $this->userInfo !== '' ? $this->userInfo . '@' : '';
        $authority .= $this->host;

        return $this->port !== null ? $authority . ':' . $this->port : $authority;
    }

    public function withScheme(string $scheme): self
    {
        return new self(strtolower($scheme), $this->userInfo, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    public function withUserInfo(string $user, ?string $password = null): self
    {
        $userInfo = $password === null ? $user : $user . ':' . $password;
        return new self($this->scheme, $userInfo, $this->host, $this->port, $this->path, $this->query, $this->fragment);
    }

    public function withHost(string $host): self
    {
        return new self($this->scheme, $this->userInfo, strtolower($host), $this->port, $this->path, $this->query, $this->fragment);
    }

    public function withPort(?int $port): self
    {
        return new self($this->scheme, $this->userInfo, $this->host, $port, $this->path, $this->query, $this->fragment);
    }

    public function withPath(string $path): self
    {
        return new self($this->scheme, $this->userInfo, $this->host, $this->port, $path, $this->query, $this->fragment);
    }

    public function withQuery(string $query): self
    {
        return new self($this->scheme, $this->userInfo, $this->host, $this->port, $this->path, ltrim($query, '?'), $this->fragment);
    }

    public function withFragment(string $fragment): self
    {
        return new self($this->scheme, $this->userInfo, $this->host, $this->port, $this->path, $this->query, ltrim($fragment, '#'));
    }

    public function toString(): string
    {
        $uri = $this->scheme !== '' ? $this->scheme . ':' : '';
        $authority = $this->authority();
        if ($authority !== '') {
            $uri .= '//' . $authority;
        }

        $path = $this->path;
        if ($authority !== '' && $path !== '' && !str_starts_with($path, '/')) {
            $path = '/' . $path;
        }
        if ($authority === '' && str_starts_with($path, '//')) {
            $path = '/' . ltrim($path, '/');
        }

        $uri .= $path;
        $uri .= $this->query !== '' ? '?' . $this->query : '';
        $uri .= $this->fragment !== '' ? '#' . $this->fragment : '';

        return $uri;
    }

    private static function assertScheme(string $scheme): void
    {
        if ($scheme !== '' && preg_match('/^[A-Za-z][A-Za-z0-9+.-]*$/', $scheme) !== 1) {
            throw new InvalidHttpUriException(sprintf('Invalid URI scheme "%s".', $scheme));
        }
    }

    private static function assertHost(string $host): void
    {
        if ($host !== '' && (preg_match('/[\x00-\x20\x7F\/#?@]/', $host) === 1 || filter_var($host, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) === false)) {
            throw new InvalidHttpUriException(sprintf('Invalid URI host "%s".', $host));
        }
    }

    private static function assertPort(?int $port): void
    {
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new InvalidHttpUriException(sprintf('Invalid URI port "%d".', $port));
        }
    }

    private static function assertPath(string $path): void
    {
        self::assertComponent($path, 'path');
        if (str_contains($path, '?') || str_contains($path, '#')) {
            throw new InvalidHttpUriException('URI path must not contain query or fragment separators.');
        }
    }

    private static function assertComponent(string $value, string $label): void
    {
        if (preg_match('/[\x00-\x1F\x7F]/', $value) === 1) {
            throw new InvalidHttpUriException(sprintf('URI %s contains control characters.', $label));
        }
    }
}
