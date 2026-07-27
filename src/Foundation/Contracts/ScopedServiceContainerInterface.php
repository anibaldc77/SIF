<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ScopeIdentifier;

interface ScopedServiceContainerInterface extends ServiceResolverInterface
{
    public function beginScope(
        ScopeIdentifier $identifier,
    ): ServiceScopeInterface;
}
