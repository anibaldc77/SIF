<?php

declare(strict_types=1);

namespace Sif\Foundation\Migration\Authorization;

use Sif\Foundation\Migration\Exceptions\MigrationAuthorizationMismatchException;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;

final class MigrationExecutionAuthorizer
{
    public function assertAuthorized(
        MigrationExecutionPlan $plan,
        MigrationExecutionAuthorization $authorization,
    ): void {
        if (!$authorization->executionAllowed()) {
            throw new MigrationAuthorizationMismatchException('Migration authorization does not permit execution.');
        }
        if ($authorization->planFingerprint() !== $plan->fingerprint()) {
            throw new MigrationAuthorizationMismatchException('Migration authorization does not match the selected plan.');
        }
        if ($authorization->direction()->value() !== $plan->direction()->value()) {
            throw new MigrationAuthorizationMismatchException('Migration authorization direction does not match the selected plan.');
        }
        if ($authorization->mode()->value() !== $plan->mode()->value()) {
            throw new MigrationAuthorizationMismatchException('Migration authorization mode does not match the selected plan.');
        }
    }
}
