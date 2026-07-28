<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Runtime;

use Sif\Foundation\Capability\Contracts\CapabilityInterface;
use Sif\Foundation\Capability\Contracts\CapabilityProviderInterface;
use Sif\Foundation\Capability\NamedCapability;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Contracts\MutableLoggingApplicationInterface;
use Sif\Foundation\Logging\Contracts\LoggerInterface;
use Sif\Foundation\ServiceProvider;

final class RuntimeLoggingServiceProvider extends ServiceProvider implements CapabilityProviderInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function register(ApplicationInterface $application): void
    {
        if ($application instanceof MutableLoggingApplicationInterface) {
            $application->setLogger($this->logger);
        }

        $this->logger->debug('Runtime logging provider registered', [
            'provider' => self::class,
            'phase' => 'register',
        ]);
    }

    public function boot(ApplicationInterface $application): void
    {
        $this->logger->info('Runtime boot completed', [
            'phase' => 'boot',
            'capabilities' => $application->capabilities(),
        ]);
    }

    public function shutdown(ApplicationInterface $application): void
    {
        $this->logger->info('Runtime shutdown reached logging provider', [
            'phase' => 'shutdown',
            'capabilities' => $application->capabilities(),
        ]);
    }

    /** @return iterable<CapabilityInterface> */
    public function capabilities(): iterable
    {
        yield new NamedCapability('logging');
    }
}
