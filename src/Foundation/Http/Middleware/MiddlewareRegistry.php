<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Middleware;

use Sif\Foundation\Contracts\HttpMiddlewareInterface;
use Sif\Foundation\Contracts\HttpMiddlewareResolverInterface;
use Sif\Foundation\Http\Exceptions\DuplicateHttpComponentException;
use Sif\Foundation\Http\Exceptions\HttpComponentNotFoundException;

final class MiddlewareRegistry implements HttpMiddlewareResolverInterface
{
    /** @var array<string, HttpMiddlewareInterface> */
    private array $middleware = [];

    public function register(string $identifier, HttpMiddlewareInterface $middleware): void
    {
        $this->assertIdentifier($identifier);
        if (isset($this->middleware[$identifier])) {
            throw new DuplicateHttpComponentException(sprintf('HTTP middleware "%s" is already registered.', $identifier));
        }
        $this->middleware[$identifier] = $middleware;
        ksort($this->middleware);
    }

    public function resolve(string $identifier): HttpMiddlewareInterface
    {
        $this->assertIdentifier($identifier);
        if (!isset($this->middleware[$identifier])) {
            throw new HttpComponentNotFoundException(sprintf('HTTP middleware "%s" is not registered.', $identifier));
        }
        return $this->middleware[$identifier];
    }

    public function count(): int
    {
        return count($this->middleware);
    }

    private function assertIdentifier(string $identifier): void
    {
        if ($identifier === '' || trim($identifier) !== $identifier || preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1) {
            throw new HttpComponentNotFoundException(sprintf('Invalid HTTP component identifier "%s".', $identifier));
        }
    }
}
