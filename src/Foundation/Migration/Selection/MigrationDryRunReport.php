<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Selection;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationDryRunReportException;

final readonly class MigrationDryRunReport
{
    /** @var list<string> */
    private array $identifiers;

    public function __construct(private MigrationExecutionPlan $plan)
    {
        $identifiers = $plan->identifiers();
        foreach ($identifiers as $identifier) {
            if ($identifier === '') {
                throw new InvalidMigrationDryRunReportException('Dry-run identifiers must be non-empty.');
            }
        }
        $this->identifiers = $identifiers;
    }

    public function plan(): MigrationExecutionPlan
    {
        return $this->plan;
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return $this->identifiers;
    }

    /** @return array{direction:string,mode:string,count:int,fingerprint:string,migrations:list<string>} */
    public function summary(): array
    {
        return [
            'direction' => $this->plan->direction()->value(),
            'mode' => $this->plan->mode()->value(),
            'count' => $this->plan->count(),
            'fingerprint' => $this->plan->fingerprint(),
            'migrations' => $this->identifiers,
        ];
    }
}
