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
use Sif\Foundation\Security\Contracts\MultiFactorChallengeLifecycleStoreInterface;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;

final readonly class MultiFactorChallengeRevokeCommand implements CliCommandInterface
{
    public function __construct(
        private MultiFactorChallengeLifecycleStoreInterface $store
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:mfa:challenge:revoke'),
            'Revokes one pending MFA challenge.',
            null,
            [
                new CliArgumentDefinition(
                    new CliArgumentName('challenge-id'),
                    'MFA challenge identifier.',
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
        $id = $invocation->argument(0);
        if ($id === null) {
            return new CliCommandResult(
                CliExitCode::invalidUsage(),
                'MFA challenge identifier is required.'
            );
        }

        $revoked = $this->store->revoke(new MultiFactorChallengeId($id));

        return new CliCommandResult(
            $revoked
                ? CliExitCode::success()
                : CliExitCode::validationFailure(),
            $revoked
                ? 'MFA challenge revoked.'
                : 'MFA challenge was not revocable.'
        );
    }
}
