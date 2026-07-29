<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer\Contracts;

use Sif\Foundation\Installer\InstallationStepIdentifier;
use Sif\Foundation\Installer\MutationClassification;
use Sif\Foundation\Installer\RollbackPolicy;
use Sif\Foundation\Installer\StepDependency;

interface InstallationStepInterface
{
    public function identifier(): InstallationStepIdentifier;

    public function description(): string;

    public function priority(): int;

    /** @return iterable<StepDependency> */
    public function dependencies(): iterable;

    public function mutationClassification(): MutationClassification;

    public function idempotent(): bool;

    public function rollbackPolicy(): RollbackPolicy;
}
