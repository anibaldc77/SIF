<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Registry;

use Sif\Foundation\Cli\Contracts\CliCommandInterface;
use Sif\Foundation\Cli\Exceptions\CliCommandNotFoundException;
use Sif\Foundation\Cli\Exceptions\DuplicateCliCommandException;
use Sif\Foundation\Cli\Value\CliCommandName;

final class CliCommandRegistry
{
    /** @var array<string, CliCommandInterface> */
    private array $commands = [];

    /** @var array<string, string> */
    private array $aliases = [];

    public function register(CliCommandInterface $command): void
    {
        $metadata = $command->metadata();
        $canonical = $metadata->name()->value();

        if (isset($this->commands[$canonical]) || isset($this->aliases[$canonical])) {
            throw new DuplicateCliCommandException(sprintf('CLI command name "%s" is already registered.', $canonical));
        }

        foreach ($metadata->aliases() as $alias) {
            $value = $alias->value();
            if (isset($this->commands[$value]) || isset($this->aliases[$value])) {
                throw new DuplicateCliCommandException(sprintf('CLI command alias "%s" is already registered.', $value));
            }
        }

        $this->commands[$canonical] = $command;
        foreach ($metadata->aliases() as $alias) {
            $this->aliases[$alias->value()] = $canonical;
        }

        ksort($this->commands);
        ksort($this->aliases);
    }

    public function has(CliCommandName|string $name): bool
    {
        $value = $name instanceof CliCommandName ? $name->value() : (new CliCommandName($name))->value();
        return isset($this->commands[$value]) || isset($this->aliases[$value]);
    }

    public function resolve(CliCommandName|string $name): CliCommandInterface
    {
        $value = $name instanceof CliCommandName ? $name->value() : (new CliCommandName($name))->value();
        $canonical = $this->aliases[$value] ?? $value;

        if (!isset($this->commands[$canonical])) {
            throw new CliCommandNotFoundException(sprintf('CLI command "%s" is not registered.', $value));
        }

        return $this->commands[$canonical];
    }

    /** @return list<CliCommandInterface> */
    public function all(): array
    {
        return array_values($this->commands);
    }

    public function count(): int
    {
        return count($this->commands);
    }
}
