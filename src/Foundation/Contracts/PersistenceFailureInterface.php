<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\PersistenceFailureKind;
use Throwable;

interface PersistenceFailureInterface extends Throwable
{
    public function kind(): PersistenceFailureKind;

    public function operation(): ?string;

    public function cause(): ?Throwable;
}
