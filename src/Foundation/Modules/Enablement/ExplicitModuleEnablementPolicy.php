<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Enablement;

use InvalidArgumentException;
use Sif\Foundation\Modules\Contracts\ModuleEnablementPolicyInterface;
use Sif\Foundation\Modules\ModuleDescriptor;

final readonly class ExplicitModuleEnablementPolicy implements ModuleEnablementPolicyInterface
{
    /**
     * @param array<string, ModuleEnablementDecision> $decisionsByModuleId
     */
    public function __construct(
        private array $decisionsByModuleId,
        private ?ModuleEnablementDecision $defaultDecision = null,
    ) {
        foreach ($decisionsByModuleId as $moduleId => $decision) {
            if (trim($moduleId) === '') {
                throw new InvalidArgumentException('Module enablement policy identifiers must be non-empty.');
            }
            if (!$decision instanceof ModuleEnablementDecision) {
                throw new InvalidArgumentException('Module enablement policy decisions must be ModuleEnablementDecision instances.');
            }
        }
    }

    public function decide(ModuleDescriptor $descriptor): ModuleEnablementDecision
    {
        return $this->decisionsByModuleId[$descriptor->id()->value()]
            ?? $this->defaultDecision
            ?? ModuleEnablementDecision::disabled('MODULE_NOT_EXPLICITLY_ENABLED');
    }
}
