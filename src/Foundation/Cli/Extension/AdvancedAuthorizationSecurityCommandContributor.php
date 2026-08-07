<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\AdvancedAuthorizationInspectCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;

final readonly class AdvancedAuthorizationSecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(
        private AdvancedAuthorizationInspectCommand $inspect
    ) {
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [$this->inspect];
    }
}
