<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

use Sif\Foundation\Model\BaseModel;

final readonly class ModelLifecycleHookCollection
{
    /** @var list<ModelLifecycleHookInterface> */
    private array $hooks;

    /** @param iterable<ModelLifecycleHookInterface> $hooks */
    public function __construct(iterable $hooks = [])
    {
        $normalized = [];
        foreach ($hooks as $hook) {
            $normalized[] = $hook;
        }

        $this->hooks = $normalized;
    }

    public function before(ModelLifecycleEvent $event): void
    {
        foreach ($this->matching($event->model(), $event->operation()) as $hook) {
            $hook->before($event);
        }
    }

    public function after(ModelLifecycleEvent $event): void
    {
        foreach ($this->matching($event->model(), $event->operation()) as $hook) {
            $hook->after($event);
        }
    }

    /** @return list<ModelLifecycleHookInterface> */
    private function matching(BaseModel $model, ModelLifecycleOperation $operation): array
    {
        $matching = [];
        foreach ($this->hooks as $hook) {
            if ($hook->supports($model, $operation)) {
                $matching[] = $hook;
            }
        }

        return $matching;
    }
}
