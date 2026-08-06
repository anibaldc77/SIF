<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\RecoveryChallengeInspectCommand;
use Sif\Foundation\Cli\Command\Security\RecoveryChallengeRevokeCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeStoreInterface;

final readonly class RecoverySecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(private RecoveryChallengeStoreInterface $store) {}
    /** @return list<CliCommandInterface> */
    public function commands(): array { return [new RecoveryChallengeInspectCommand($this->store),new RecoveryChallengeRevokeCommand($this->store)]; }
}
