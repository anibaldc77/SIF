<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Command;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Contract\VersionProviderInterface;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;

final readonly class VersionCommand implements CommandInterface
{
    public function __construct(private VersionProviderInterface $versionProvider)
    {
    }

    public function name(): string
    {
        return 'version';
    }

    public function description(): string
    {
        return 'Display the SIF Builder version.';
    }

    public function execute(CommandInput $input): CommandResult
    {
        if ($input->arguments !== [] || $input->options !== [] || $input->flags !== []) {
            return CommandResult::failure(
                ExitCode::INVALID_USAGE,
                'The version command does not accept arguments or options.',
            );
        }

        return CommandResult::success(sprintf(
            "%s %s\n",
            $this->versionProvider->applicationName(),
            $this->versionProvider->version(),
        ));
    }
}
