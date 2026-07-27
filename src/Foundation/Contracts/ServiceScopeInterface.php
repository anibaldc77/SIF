<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Container\ScopeIdentifier;

interface ServiceScopeInterface extends
    ScopedServiceContainerInterface,
    TaggedServiceLocatorInterface
{
    public function identifier(): ScopeIdentifier;

    public function parent(): ?ServiceScopeInterface;

    public function isClosed(): bool;

    public function close(): void;
}
