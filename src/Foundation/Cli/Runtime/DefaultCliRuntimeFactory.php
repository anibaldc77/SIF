<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Runtime;

use Sif\Foundation\Cli\Command\Runtime\RuntimeAboutCommand;
use Sif\Foundation\Cli\Command\Runtime\RuntimeCapabilitiesCommand;
use Sif\Foundation\Cli\Command\Runtime\RuntimeDoctorCommand;
use Sif\Foundation\Cli\Console\CliConsoleKernel;
use Sif\Foundation\Cli\Extension\CliCommandContributorCollection;
use Sif\Foundation\Cli\Help\CliHelpCatalog;
use Sif\Foundation\Cli\Parsing\CliInvocationParser;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;
use Sif\Foundation\Cli\Rendering\CliCommandResultRenderer;
use Sif\Foundation\Cli\Rendering\CliOutputFormat;
use Sif\Foundation\Configuration\Contracts\ConfigurationInterface;
use Sif\Foundation\Contracts\RuntimeInterface;

final readonly class DefaultCliRuntimeFactory
{
    /**
     * @param list<string> $capabilities
     */
    public function __construct(
        private RuntimeInterface $runtime,
        private ConfigurationInterface $configuration,
        private array $capabilities = [],
        private ?CliCommandContributorCollection $contributors = null,
    ) {
    }

    public function create(): CliRuntime
    {
        $registry = new CliCommandRegistry();
        $registry->register(new RuntimeAboutCommand($this->runtime, $this->capabilities));
        $registry->register(new RuntimeCapabilitiesCommand($this->capabilities));
        $registry->register(new RuntimeDoctorCommand($this->runtime, $this->configuration));
        $this->contributors?->registerInto($registry);

        $parser = new CliInvocationParser($registry);
        $renderer = new CliCommandResultRenderer(CliOutputFormat::text());
        $kernel = new CliConsoleKernel($registry, $parser, $renderer);

        return new CliRuntime($kernel, $registry, new CliHelpCatalog($registry));
    }
}
