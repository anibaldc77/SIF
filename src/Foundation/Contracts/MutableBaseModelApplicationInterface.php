<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Model\Runtime\BaseModelRuntime;

interface MutableBaseModelApplicationInterface extends BaseModelAwareApplicationInterface
{
    public function setModels(BaseModelRuntime $models): void;
}
