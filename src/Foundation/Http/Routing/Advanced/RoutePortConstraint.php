<?php

declare(strict_types=1);

namespace Sif\Foundation\Http\Routing\Advanced;

use Sif\Foundation\Http\Exceptions\RouteTransportConstraintException;

final readonly class RoutePortConstraint
{
    public function __construct(private int $port)
    {
        if ($port < 1 || $port > 65535) {
            throw new RouteTransportConstraintException(sprintf('Invalid route port "%d".', $port));
        }
    }

    public function value(): int { return $this->port; }
    public function matches(?int $port, string $scheme): bool
    {
        $effective = $port ?? match (strtolower($scheme)) {
            'http' => 80,
            'https' => 443,
            default => null,
        };
        return $effective === $this->port;
    }
}
