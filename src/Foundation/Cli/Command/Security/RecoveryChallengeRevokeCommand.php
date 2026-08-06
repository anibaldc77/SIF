<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Security;

use DateTimeImmutable;
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

final readonly class RecoveryChallengeRevokeCommand implements CliCommandInterface
{
    public function __construct(private RecoveryChallengeStoreInterface $store) {}
    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(new CliCommandName('security:recovery:revoke'),'Revokes one recovery challenge.',null,[new CliArgumentDefinition(new CliArgumentName('challenge-id'),'Challenge identifier.',true)],[],CliOperationalClass::mutation(),false,true);
    }
    public function execute(CliInvocation $invocation): CliCommandResult
    {
        $id=$invocation->argument(0); if ($id===null) return new CliCommandResult(CliExitCode::invalidUsage(),'Challenge identifier is required.');
        $revoked=$this->store->revoke(new RecoveryChallengeId($id),new DateTimeImmutable('now'));
        return new CliCommandResult($revoked?CliExitCode::success():CliExitCode::validationFailure(),$revoked?'Recovery challenge revoked.':'Recovery challenge was not revocable.');
    }
}
