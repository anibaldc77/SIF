<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Dispatch;

use Sif\Foundation\Contracts\HttpHandlerResolverInterface;
use Sif\Foundation\Contracts\RequestHandlerInterface;
use Sif\Foundation\Http\Exceptions\DuplicateHttpComponentException;
use Sif\Foundation\Http\Exceptions\HttpComponentNotFoundException;

final class HandlerRegistry implements HttpHandlerResolverInterface
{
    /** @var array<string, RequestHandlerInterface> */
    private array $handlers = [];

    public function register(string $identifier, RequestHandlerInterface $handler): void
    {
        $this->assertIdentifier($identifier);
        if (isset($this->handlers[$identifier])) {
            throw new DuplicateHttpComponentException(sprintf('HTTP handler "%s" is already registered.', $identifier));
        }
        $this->handlers[$identifier] = $handler;
        ksort($this->handlers);
    }

    public function resolve(string $identifier): RequestHandlerInterface
    {
        $this->assertIdentifier($identifier);
        if (!isset($this->handlers[$identifier])) {
            throw new HttpComponentNotFoundException(sprintf('HTTP handler "%s" is not registered.', $identifier));
        }
        return $this->handlers[$identifier];
    }

    public function count(): int
    {
        return count($this->handlers);
    }

    private function assertIdentifier(string $identifier): void
    {
        if ($identifier === '' || trim($identifier) !== $identifier || preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $identifier) !== 1) {
            throw new HttpComponentNotFoundException(sprintf('Invalid HTTP component identifier "%s".', $identifier));
        }
    }
}
