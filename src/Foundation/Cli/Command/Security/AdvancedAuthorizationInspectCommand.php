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
use Sif\Foundation\Security\Authorization\Diagnostics\AdvancedAuthorizationDiagnosticService;
use Sif\Foundation\Security\Authorization\Integration\AdvancedAuthorizationRequest;

final readonly class AdvancedAuthorizationInspectCommand implements CliCommandInterface
{
    public function __construct(
        private AdvancedAuthorizationDiagnosticService $diagnostics,
        private AdvancedAuthorizationRequest $request
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:authorization:inspect'),
            'Inspects one advanced authorization evaluation using sanitized diagnostics.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            false
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $snapshot = $this->diagnostics->evaluate(
            $this->request->principal(),
            $this->request->resource(),
            $this->request->environment()
        );

        return new CliCommandResult(
            CliExitCode::success(),
            'Advanced authorization evaluation',
            ['authorization' => $snapshot->toArray()]
        );
    }
}
