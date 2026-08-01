<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Lifecycle;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Model\BaseModel;

final readonly class ModelLifecycleEvent
{
    /**
     * @param array<string, mixed> $before
     * @param array<string, mixed> $after
     * @param array<string, mixed> $changes
     */
    public function __construct(
        private BaseModel $model,
        private ModelLifecycleOperation $operation,
        private ModelLifecyclePhase $phase,
        private ExecutionContextInterface $context,
        private array $before = [],
        private array $after = [],
        private array $changes = [],
    ) {
    }

    public function model(): BaseModel
    {
        return $this->model;
    }

    public function operation(): ModelLifecycleOperation
    {
        return $this->operation;
    }

    public function phase(): ModelLifecyclePhase
    {
        return $this->phase;
    }

    public function context(): ExecutionContextInterface
    {
        return $this->context;
    }

    /** @return array<string, mixed> */
    public function before(): array
    {
        return $this->before;
    }

    /** @return array<string, mixed> */
    public function after(): array
    {
        return $this->after;
    }

    /** @return array<string, mixed> */
    public function changes(): array
    {
        return $this->changes;
    }
}
