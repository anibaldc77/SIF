<?php

declare(strict_types=1);

namespace Sif\Foundation\ApplicationSkeleton\Cli;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliArgumentDefinition;
use Sif\Foundation\Cli\Value\CliArgumentName;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Cli\Value\CliOptionDefinition;
use Sif\Foundation\Cli\Value\CliOptionName;

final readonly class ApplicationCreateCommand implements CliCommandInterface
{
    public function __construct(private ApplicationCreationOperations $operations)
    {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('app:create'),
            'Plans or creates a new SIF application skeleton.',
            'Creation is a dry-run by default. --execute requests execution but does not itself authorize mutations.',
            [new CliArgumentDefinition(new CliArgumentName('target'), 'Existing target directory.', true)],
            [new CliOptionDefinition(new CliOptionName('execute'), 'Request execution of an authorized plan.')],
            CliOperationalClass::mutation(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $result = $this->operations->create($invocation);
        $plan = $result['plan'];
        $data = [
            'plan_fingerprint' => $plan->fingerprint(),
            'executable' => $plan->executable(),
            'executed' => $result['executed'],
            'authorized' => $result['authorized'],
            'entries' => $plan->summary(),
        ];

        if (!$plan->executable()) {
            return new CliCommandResult(CliExitCode::validationFailure(), 'Application skeleton plan contains conflicts.', $data);
        }

        if ($invocation->hasOption('execute') && !$result['authorized']) {
            return new CliCommandResult(CliExitCode::notAuthorized(), 'Application skeleton execution was not authorized.', $data);
        }

        return new CliCommandResult(
            CliExitCode::success(),
            $result['executed'] ? 'Application skeleton created.' : 'Application skeleton plan created.',
            $data,
        );
    }
}
