<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Command\Session\CsrfConfigurationInspectCommand;
use Sif\Foundation\Cli\Command\Session\SessionConfigurationInspectCommand;
use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Security\Csrf\CsrfConfiguration;
use Sif\Foundation\Session\Policy\SessionRegenerationPolicy;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\Transport\SessionCookieConfiguration;

final readonly class SessionSecurityCommandContributor implements CliCommandContributorInterface
{
    public function __construct(
        private SessionCookieConfiguration $cookie,
        private SessionPolicy $session,
        private SessionRegenerationPolicy $regeneration,
        private CsrfConfiguration $csrf,
    ) {
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        return [
            new CsrfConfigurationInspectCommand($this->csrf),
            new SessionConfigurationInspectCommand($this->cookie, $this->session, $this->regeneration),
        ];
    }
}
