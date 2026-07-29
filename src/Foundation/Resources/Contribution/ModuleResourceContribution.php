<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources\Contribution;

use Sif\Foundation\Modules\ModuleId;
use Sif\Foundation\Resources\ResourceDescriptor;

final readonly class ModuleResourceContribution
{
    public function __construct(
        private ModuleId $moduleId,
        private ResourceDescriptor $descriptor,
        private ResourceOverridePolicy $overridePolicy = new ResourceOverridePolicy(),
    ) {
    }

    public function moduleId(): ModuleId { return $this->moduleId; }
    public function descriptor(): ResourceDescriptor { return $this->descriptor; }
    public function overridePolicy(): ResourceOverridePolicy { return $this->overridePolicy; }
}
