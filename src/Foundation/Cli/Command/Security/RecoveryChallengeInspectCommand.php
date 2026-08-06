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
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;

final readonly class RecoveryChallengeInspectCommand implements CliCommandInterface
{
    public function __construct(private RecoveryChallengeStoreInterface $store) {}
    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(new CliCommandName('security:recovery:inspect'),'Inspects a recovery challenge without exposing token material.',null,[new CliArgumentDefinition(new CliArgumentName('challenge-id'),'Challenge identifier.',true)],[],CliOperationalClass::inspection(),false,false);
    }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $id=$invocation->argument(0); if ($id===null) return new CliCommandResult(CliExitCode::invalidUsage(),'Challenge identifier is required.');
        $record=$this->store->find(new RecoveryChallengeId($id));
        if ($record===null) return new CliCommandResult(CliExitCode::validationFailure(),'Recovery challenge not found.');
        return new CliCommandResult(CliExitCode::success(),'Recovery challenge',[ 'challenge'=>$record->challenge()->snapshot(), 'state'=>$record->state()->value ]);
    }
}
