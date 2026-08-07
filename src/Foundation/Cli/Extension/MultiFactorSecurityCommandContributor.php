<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\MultiFactorChallengeInspectCommand;
use Sif\Foundation\Cli\Command\Security\MultiFactorChallengeRevokeCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Security\Contracts\MultiFactorChallengeLifecycleStoreInterface;

final readonly class MultiFactorSecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(
        private MultiFactorChallengeLifecycleStoreInterface $store
    ) {
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [
            new MultiFactorChallengeInspectCommand($this->store),
            new MultiFactorChallengeRevokeCommand($this->store),
        ];
    }
}
