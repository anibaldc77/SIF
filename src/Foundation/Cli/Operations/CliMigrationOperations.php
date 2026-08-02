<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Operations;

use Closure;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Migration\Authorization\MigrationExecutionAuthorization;
use Sif\Foundation\Migration\MigrationDirection;
use Sif\Foundation\Migration\MigrationExecutionMode;
use Sif\Foundation\Migration\MigrationRequest;
use Sif\Foundation\Migration\Runtime\MigrationRuntime;
use Sif\Foundation\Migration\Selection\MigrationExecutionPlan;

final readonly class CliMigrationOperations
{
    /**
     * @param Closure(CliInvocation, MigrationDirection, MigrationExecutionMode): MigrationRequest $requestFactory
     * @param Closure(MigrationExecutionPlan, CliInvocation): MigrationExecutionAuthorization|null $authorizationFactory
     */
    public function __construct(
        private MigrationRuntime $runtime,
        private Closure $requestFactory,
        private ?Closure $authorizationFactory = null,
    ) {
    }

    public function runtime(): MigrationRuntime { return $this->runtime; }

    public function request(
        CliInvocation $invocation,
        MigrationDirection $direction,
        MigrationExecutionMode $mode,
    ): MigrationRequest {
        return ($this->requestFactory)($invocation, $direction, $mode);
    }

    public function authorization(
        MigrationExecutionPlan $plan,
        CliInvocation $invocation,
    ): ?MigrationExecutionAuthorization {
        return $this->authorizationFactory === null
            ? null
            : ($this->authorizationFactory)($plan, $invocation);
    }
}
