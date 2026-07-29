<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Contracts\InstallationStepInterface;
use Sif\Foundation\Installer\Exceptions\InvalidInstallationStepPlanException;

final readonly class InstallationStepPlan
{
    /** @var list<InstallationStepInterface> */
    private array $steps;

    /** @param iterable<InstallationStepInterface> $steps */
    public function __construct(iterable $steps)
    {
        $normalized = [];
        $seen = [];
        foreach ($steps as $step) {
            if (!$step instanceof InstallationStepInterface) {
                throw new InvalidInstallationStepPlanException('Installation step plan members must implement InstallationStepInterface.');
            }
            $identifier = $step->identifier()->value();
            if (isset($seen[$identifier])) {
                throw new InvalidInstallationStepPlanException(sprintf('Duplicate installation step "%s" in compiled plan.', $identifier));
            }
            $seen[$identifier] = true;
            $normalized[] = $step;
        }
        $this->steps = $normalized;
    }

    /** @return list<InstallationStepInterface> */
    public function steps(): array
    {
        return $this->steps;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return array_map(static fn (InstallationStepInterface $step): string => $step->identifier()->value(), $this->steps);
    }

    /** @return list<array{identifier:string,description:string,priority:int,mutation:string,idempotent:bool,rollback:string}> */
    public function summary(): array
    {
        return array_map(static fn (InstallationStepInterface $step): array => [
            'identifier' => $step->identifier()->value(),
            'description' => $step->description(),
            'priority' => $step->priority(),
            'mutation' => $step->mutationClassification()->value(),
            'idempotent' => $step->idempotent(),
            'rollback' => $step->rollbackPolicy()->value(),
        ], $this->steps);
    }
}
