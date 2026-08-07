<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Security\PersistentAuthenticationInspectCommand;
use Sif\Foundation\Cli\Command\Security\PersistentAuthenticationRevokeCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialLifecycleStoreInterface;

final readonly class PersistentAuthenticationSecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(
        private PersistentAuthenticationCredentialLifecycleStoreInterface $store
    ) {
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [
            new PersistentAuthenticationInspectCommand($this->store),
            new PersistentAuthenticationRevokeCommand($this->store),
        ];
    }
}
