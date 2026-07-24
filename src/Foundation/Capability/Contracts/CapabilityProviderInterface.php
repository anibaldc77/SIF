<?php

declare(strict_types=1);

namespace Sif\Foundation\Capability\Contracts;

/** Optional provider contract for capabilities discovered during boot. */
interface CapabilityProviderInterface
{
    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable;
}
