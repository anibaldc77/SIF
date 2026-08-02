<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Help;

use Sif\Foundation\Cli\Registry\CliCommandRegistry;

final readonly class CliHelpCatalog
{
    public function __construct(private CliCommandRegistry $registry)
    {
    }

    /** @return list<CliCommandHelp> */
    public function commands(): array
    {
        return array_map(
            static fn ($command): CliCommandHelp => new CliCommandHelp($command->metadata()),
            $this->registry->all(),
        );
    }
}
