<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Security\Context\SecurityContext;

final readonly class SecurityContextInspectCommand implements CliCommandInterface
{
    public function __construct(private SecurityContext $context)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:context'),
            'Inspects the current security context without exposing credentials.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $principal = $this->context->principal();

        return new CliCommandResult(
            CliExitCode::success(),
            'Security context',
            [
                'authenticated' => $principal->isAuthenticated(),
                'state' => $principal->authenticationState()->value,
            ],
        );
    }
}
