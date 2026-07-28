<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Exceptions;

final class ModuleContributionCollisionException extends ModuleRegistryException
{
    public static function forContribution(string $type, string $identifier, string $owner, string $challenger): self
    {
        return new self(sprintf(
            'Module contribution collision for %s "%s" between modules "%s" and "%s".',
            $type,
            $identifier,
            $owner,
            $challenger,
        ));
    }
}
