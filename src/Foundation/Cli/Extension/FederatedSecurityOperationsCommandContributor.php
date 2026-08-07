<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\FederatedRevocationExecuteCommand;
use Sif\Foundation\Cli\Command\Security\FederatedRevocationInspectCommand;

final readonly class FederatedSecurityOperationsCommandContributor
{
    public function __construct(
        private FederatedRevocationInspectCommand $inspectCommand,
        private FederatedRevocationExecuteCommand $executeCommand
    ) {
    }

    public function inspectCommand(): FederatedRevocationInspectCommand
    {
        return $this->inspectCommand;
    }

    public function executeCommand(): FederatedRevocationExecuteCommand
    {
        return $this->executeCommand;
    }
}
