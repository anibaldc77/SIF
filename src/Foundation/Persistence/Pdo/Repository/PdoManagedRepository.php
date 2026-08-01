<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence\Pdo\Repository;

interface PdoManagedRepository
{
    public function managedType(): string;

    public function supports(object $object): bool;

    public function saveObject(object $object): void;

    public function removeObject(object $object): void;
}
