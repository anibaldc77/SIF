<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Routing;

use InvalidArgumentException;
use Sif\Foundation\Logging\Contracts\LogHandlerInterface;
use Sif\Foundation\Logging\Contracts\LogRecordFilterInterface;

final readonly class LogRoute
{
    public function __construct(
        private string $name,
        private LogRecordFilterInterface $filter,
        private LogHandlerInterface $handler,
    ) {
        if (preg_match('/^[a-z][a-z0-9]*(?:[._-][a-z0-9]+)*$/', $name) !== 1) {
            throw new InvalidArgumentException(sprintf('Invalid log route name "%s".', $name));
        }
    }

    public function name(): string { return $this->name; }
    public function filter(): LogRecordFilterInterface { return $this->filter; }
    public function handler(): LogHandlerInterface { return $this->handler; }
}
