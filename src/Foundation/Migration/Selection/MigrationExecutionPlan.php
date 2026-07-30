<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Selection;

use Sif\Foundation\Migration\Exceptions\InvalidMigrationExecutionPlanException;
use Sif\Foundation\Migration\MigrationDescriptor;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Planning\MigrationPlan;

final readonly class MigrationExecutionPlan
{
    private string $fingerprint;

    public function __construct(
        private MigrationRequest $request,
        private MigrationPlan $plan,
    ) {
        if ($request->direction()->value() !== $plan->direction()->value()) {
            throw new InvalidMigrationExecutionPlanException('Migration request and plan directions must match.');
        }

        $payload = [
            'request' => $request->summary(),
            'plan_fingerprint' => $plan->fingerprint(),
        ];
        $this->fingerprint = hash('sha256', json_encode($payload, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES));
    }

    public function request(): MigrationRequest
    {
        return $this->request;
    }

    public function direction(): MigrationDirection
    {
        return $this->request->direction();
    }

    public function mode(): MigrationExecutionMode
    {
        return $this->request->mode();
    }

    /** @return list<MigrationDescriptor> */
    public function migrations(): array
    {
        return $this->plan->migrations();
    }

    /** @return list<string> */
    public function identifiers(): array
    {
        return $this->plan->identifiers();
    }

    public function count(): int
    {
        return $this->plan->count();
    }

    public function fingerprint(): string
    {
        return $this->fingerprint;
    }
}
