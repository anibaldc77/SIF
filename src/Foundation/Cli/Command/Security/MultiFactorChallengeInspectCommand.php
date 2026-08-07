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
use Sif\Foundation\Security\Contracts\MultiFactorChallengeStoreInterface;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId;

final readonly class MultiFactorChallengeInspectCommand implements CliCommandInterface
{
    public function __construct(private MultiFactorChallengeStoreInterface $store)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('security:mfa:challenge:inspect'),
            'Inspects an MFA challenge without exposing factor secrets.',
            null,
            [
                new CliArgumentDefinition(
                    new CliArgumentName('challenge-id'),
                    'MFA challenge identifier.',
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
        $id = $invocation->argument(0);
        if ($id === null) {
            return new CliCommandResult(
                CliExitCode::invalidUsage(),
                'MFA challenge identifier is required.'
            );
        }

        $challenge = $this->store->find(new MultiFactorChallengeId($id));
        if ($challenge === null) {
            return new CliCommandResult(
                CliExitCode::validationFailure(),
                'MFA challenge not found.'
            );
        }

        return new CliCommandResult(
            CliExitCode::success(),
            'MFA challenge',
            ['challenge' => $challenge->snapshot()]
        );
    }
}
