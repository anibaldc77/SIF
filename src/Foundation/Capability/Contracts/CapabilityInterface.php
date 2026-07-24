<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability\Contracts;

/**
 * Identifies a Runtime capability through a stable framework-level key.
 */
interface CapabilityInterface
{
    /**
     * Returns the stable, non-empty identifier used by the registry.
     */
    public function identifier(): string;
}
