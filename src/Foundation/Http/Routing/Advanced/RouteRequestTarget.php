<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Http\Exceptions\RouteTransportConstraintException;
use Sif\Foundation\Http\Value\HttpMethod;

final readonly class RouteRequestTarget
{
    private string $scheme;
    private string $host;

    public function __construct(
        private HttpMethod $method,
        private string $path,
        string $scheme = '',
        string $host = '',
        private ?int $port = null,
    ) {
        if ($path === '' || $path[0] !== '/') {
            throw new RouteTransportConstraintException('Route request target path must begin with "/".');
        }
        $scheme = strtolower($scheme);
        if ($scheme !== '' && !in_array($scheme, ['http', 'https'], true)) {
            throw new RouteTransportConstraintException(sprintf('Unsupported request target scheme "%s".', $scheme));
        }
        if ($port !== null && ($port < 1 || $port > 65535)) {
            throw new RouteTransportConstraintException(sprintf('Invalid request target port "%d".', $port));
        }
        $this->scheme = $scheme;
        $this->host = strtolower($host);
    }

    public static function fromRequest(RequestInterface $request): self
    {
        $uri = $request->uri();
        return new self($request->method(), $uri->path() === '' ? '/' : $uri->path(), $uri->scheme(), $uri->host(), $uri->port());
    }

    public function method(): HttpMethod { return $this->method; }
    public function path(): string { return $this->path; }
    public function scheme(): string { return $this->scheme; }
    public function host(): string { return $this->host; }
    public function port(): ?int { return $this->port; }
}
