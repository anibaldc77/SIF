<?php

declare(strict_types=1);

namespace Sif\Foundation\Modules\Planning;

use InvalidArgumentException;
use Sif\Foundation\Modules\ModuleDescriptor;

final readonly class DisabledModule
{
    public function __construct(
        private ModuleDescriptor $descriptor,
        private string $reasonCode,
    ) {
        if (trim($reasonCode) === '') {
            throw new InvalidArgumentException('Disabled module reason code must be non-empty.');
        }
    }

    public function descriptor(): ModuleDescriptor
    {
        return $this->descriptor;
    }

    public function reasonCode(): string
    {
        return $this->reasonCode;
    }
}
