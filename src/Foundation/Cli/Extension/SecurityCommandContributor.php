<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\SecurityContextInspectCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Security\Context\SecurityContext;

final readonly class SecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(private SecurityContext $context)
    {
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [new SecurityContextInspectCommand($this->context)];
    }
}
