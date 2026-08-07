<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use DateTimeImmutable;
use DateTimeZone;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialLifecycleStoreInterface;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationSelector;

final readonly class PersistentAuthenticationRevokeCommand implements CliCommandInterface
{
    public function __construct(
        private PersistentAuthenticationCredentialLifecycleStoreInterface $store
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:persistent:revoke'),
            'Revokes one persistent authentication credential.',
            null,
            [
                new CliArgumentDefinition(
                    new CliArgumentName('selector'),
                    'Persistent authentication selector.',
                    true
                ),
            ],
            [],
            CliOperationalClass::mutation(),
            false,
            true
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

        $revoked = $this->store->revoke(
            new PersistentAuthenticationSelector($selector),
            new DateTimeImmutable('now', new DateTimeZone('UTC'))
        );

        return new CliCommandResult(
            $revoked
                ? CliExitCode::success()
                : CliExitCode::validationFailure(),
            $revoked
                ? 'Persistent authentication credential revoked.'
                : 'Persistent authentication credential was not revocable.'
        );
    }
}
