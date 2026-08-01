<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

use Sif\Foundation\Model\BaseModel;

interface ModelLifecycleHookInterface
{
    public function supports(BaseModel $model, ModelLifecycleOperation $operation): bool;

    public function before(ModelLifecycleEvent $event): void;

    public function after(ModelLifecycleEvent $event): void;
}
