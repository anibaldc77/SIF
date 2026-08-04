<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Command\Session;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Value\CliCommandMetadata;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliCommandResult;
use Sif\Foundation\Cli\Value\CliExitCode;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Cli\Value\CliOperationalClass;
use Sif\Foundation\Session\Policy\SessionRegenerationPolicy;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\Transport\SessionCookieConfiguration;

final readonly class SessionConfigurationInspectCommand implements CliCommandInterface
{
    public function __construct(
        private SessionCookieConfiguration $cookie,
        private SessionPolicy $policy,
        private SessionRegenerationPolicy $regeneration,
    ) {
    }

    public function metadata(): CliCommandMetadata
    {
        return new CliCommandMetadata(
            new CliCommandName('session:config'),
            'Inspects safe session and cookie configuration.',
            null,
            [],
            [],
            CliOperationalClass::inspection(),
            false,
            false,
        );
    }

    public function execute(CliInvocation $invocation): CliCommandResult
    {
        return new CliCommandResult(
            CliExitCode::success(),
            'Session configuration',
            [
                'cookie' => [
                    'name' => $this->cookie->name(),
                    'path' => $this->cookie->path(),
                    'domain' => $this->cookie->domain(),
                    'secure' => $this->cookie->secure(),
                    'http_only' => $this->cookie->httpOnly(),
                    'same_site' => $this->cookie->sameSite()->value,
                    'max_age' => $this->cookie->maxAge(),
                ],
                'absolute_lifetime' => $this->policy->absoluteLifetimeSeconds(),
                'idle_lifetime' => $this->policy->idleLifetimeSeconds(),
                'regeneration_interval' => $this->regeneration->intervalSeconds(),
            ],
        );
    }
}
