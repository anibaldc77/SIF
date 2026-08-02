<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Operations;

use Closure;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Installer\ExecutionAuthorization;
use Sif\Foundation\Installer\InstallationDryRunReport;
use Sif\Foundation\Installer\InstallationRequest;
use Sif\Foundation\Installer\Mutations\MutationPlan;
use Sif\Foundation\Installer\Runtime\InstallerRuntime;

final readonly class CliInstallerOperations
{
    /**
     * @param Closure(CliInvocation): InstallationRequest $requestFactory
     * @param Closure(InstallationRequest, CliInvocation): MutationPlan $mutationPlanFactory
     * @param Closure(InstallationDryRunReport, CliInvocation): ExecutionAuthorization|null $authorizationFactory
     */
    public function __construct(
        private InstallerRuntime $runtime,
        private Closure $requestFactory,
        private Closure $mutationPlanFactory,
        private ?Closure $authorizationFactory = null,
    ) {
    }

    public function runtime(): InstallerRuntime { return $this->runtime; }
    public function request(CliInvocation $invocation): InstallationRequest { return ($this->requestFactory)($invocation); }
    public function mutations(InstallationRequest $request, CliInvocation $invocation): MutationPlan { return ($this->mutationPlanFactory)($request, $invocation); }
    public function authorization(InstallationDryRunReport $dryRun, CliInvocation $invocation): ?ExecutionAuthorization
    {
        return $this->authorizationFactory === null ? null : ($this->authorizationFactory)($dryRun, $invocation);
    }
}
