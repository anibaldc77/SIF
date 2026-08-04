<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Session;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Security\Csrf\CsrfConfiguration;

final readonly class CsrfConfigurationInspectCommand implements CliCommandInterface
{
    public function __construct(private CsrfConfiguration $configuration)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('csrf:config'),
            'Inspects safe CSRF configuration without exposing tokens.',
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
        return new CliCommandResult(
            CliExitCode::success(),
            'CSRF configuration',
            [
                'header_name' => $this->configuration->headerName(),
                'body_field' => $this->configuration->bodyField(),
                'protected_methods' => array_map(
                    static fn ($method): string => $method->value,
                    $this->configuration->protectedMethods(),
                ),
            ],
        );
    }
}
