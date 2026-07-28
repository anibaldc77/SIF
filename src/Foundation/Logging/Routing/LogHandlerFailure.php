<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Routing;

use Throwable;

final readonly class LogHandlerFailure
{
    public function __construct(private string $route, private Throwable $cause)
    {
    }

    public function route(): string { return $this->route; }
    public function cause(): Throwable { return $this->cause; }
}
