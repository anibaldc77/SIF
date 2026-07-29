<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Contracts;

use Sif\Foundation\Installer\Execution\MutationExecutionResult;
use Sif\Foundation\Installer\Mutations\MutationDescriptor;

interface MutationHandlerInterface
{
    public function supports(MutationDescriptor $mutation): bool;

    public function apply(MutationDescriptor $mutation): MutationExecutionResult;

    public function compensate(
        MutationDescriptor $mutation,
        MutationExecutionResult $appliedResult,
    ): MutationExecutionResult;
}
