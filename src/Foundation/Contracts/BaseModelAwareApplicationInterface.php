<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Model\Runtime\BaseModelRuntime;

interface BaseModelAwareApplicationInterface extends ApplicationInterface
{
    public function models(): ?BaseModelRuntime;
}
