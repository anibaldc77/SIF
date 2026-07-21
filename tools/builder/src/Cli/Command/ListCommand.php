<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Command;

use Sif\Builder\Cli\CommandResult;
use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Contract\ComponentCatalogInterface;
use Sif\Builder\Cli\ExitCode;
use Sif\Builder\Cli\Input\CommandInput;

final readonly class ListCommand implements CommandInterface
{
    public function __construct(private ComponentCatalogInterface $catalog)
    {
    }

    public function name(): string
    {
        return 'list';
    }

    public function description(): string
    {
        return 'List registered analyzers, generators, and reporters.';
    }

    public function execute(CommandInput $input): CommandResult
    {
        if ($input->arguments !== [] || $input->options !== [] || $input->flags !== []) {
            return CommandResult::failure(
                ExitCode::INVALID_USAGE,
                'The list command does not accept arguments or options.',
            );
        }

        return CommandResult::success(implode("\n", [
            'Analyzers:',
            ...$this->renderIdentifiers($this->catalog->analyzers()),
            '',
            'Generators:',
            ...$this->renderIdentifiers($this->catalog->generators()),
            '',
            'Reporters:',
            ...$this->renderIdentifiers($this->catalog->reporters()),
            '',
        ]));
    }

    /** @param list<string> $identifiers @return list<string> */
    private function renderIdentifiers(array $identifiers): array
    {
        if ($identifiers === []) {
            return ['  (none)'];
        }

        return array_map(
            static fn (string $identifier): string => '  - ' . $identifier,
            $identifiers,
        );
    }
}
