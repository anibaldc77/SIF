<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\UnitOfWorkState;

interface UnitOfWorkInterface
{
    public function registerNew(object $object): void;

    public function registerDirty(object $object): void;

    public function registerRemoved(object $object): void;

    public function commit(): void;

    public function clear(): void;

    public function state(): UnitOfWorkState;

    public function isEmpty(): bool;
}
