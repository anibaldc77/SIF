<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;

/** Immutable nominal capability used by the legacy string facade. */
final readonly class NamedCapability implements CapabilityInterface
{
    public function __construct(private string $identifier)
    {
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}
