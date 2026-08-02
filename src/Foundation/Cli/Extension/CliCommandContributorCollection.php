<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Extension;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Registry\CliCommandRegistry;

final readonly class CliCommandContributorCollection
{
    /** @var list<CliCommandContributorInterface> */
    private array $contributors;

    /** @param list<CliCommandContributorInterface> $contributors */
    public function __construct(array $contributors = [])
    {
        $this->contributors = array_values($contributors);
    }

    /** @return list<CliCommandInterface> */
    public function commands(): array
    {
        $commands = [];
        foreach ($this->contributors as $contributor) {
            foreach ($contributor->commands() as $command) {
                $commands[] = $command;
            }
        }

        usort(
            $commands,
            static fn (CliCommandInterface $left, CliCommandInterface $right): int =>
                $left->metadata()->name()->value() <=> $right->metadata()->name()->value(),
        );

        return $commands;
    }

    public function registerInto(CliCommandRegistry $registry): void
    {
        foreach ($this->commands() as $command) {
            $registry->register($command);
        }
    }

    public function count(): int
    {
        return count($this->contributors);
    }
}
