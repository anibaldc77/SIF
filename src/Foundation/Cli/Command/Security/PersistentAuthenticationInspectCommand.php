<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialStoreInterface;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;

final readonly class PersistentAuthenticationInspectCommand implements CliCommandInterface
{
    public function __construct(
        private PersistentAuthenticationCredentialStoreInterface $store
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:persistent:inspect'),
            'Inspects a persistent authentication credential without exposing reusable material.',
            null,
            [
                new CliArgumentDefinition(
                    new CliArgumentName('selector'),
                    'Persistent authentication selector.',
                    true
                ),
            ],
            [],
            CliOperationalClass::inspection(),
            false,
            false
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $selector = $invocation->argument(0);

        if ($selector === null) {
            return new CliCommandResult(
                CliExitCode::invalidUsage(),
                'Persistent authentication selector is required.'
            );
        }

        $credential = $this->store->findBySelector(
            new PersistentAuthenticationSelector($selector)
        );

        if ($credential === null) {
            return new CliCommandResult(
                CliExitCode::validationFailure(),
                'Persistent authentication credential not found.'
            );
        }

        return new CliCommandResult(
            CliExitCode::success(),
            'Persistent authentication credential',
            ['credential' => $credential->snapshot()]
        );
    }
}
