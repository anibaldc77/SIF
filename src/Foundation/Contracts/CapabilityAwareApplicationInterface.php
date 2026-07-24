<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Capability\CapabilityRegistry;
use Sif\Foundation\Capability\Contracts\CapabilityInterface;

/** Extended application contract exposing typed capability resolution. */
interface CapabilityAwareApplicationInterface extends ApplicationInterface
{
    public function capabilityRegistry(): CapabilityRegistry;

    public function registerCapability(CapabilityInterface $capability): void;

    public function capability(string $identifier): CapabilityInterface;
}
