<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Registry;

use Sif\Builder\Cli\Contract\CommandInterface;
use Sif\Builder\Cli\Exception\CommandRegistryFrozenException;
use Sif\Builder\Cli\Exception\DuplicateCommandException;
use Sif\Builder\Cli\Exception\InvalidCommandNameException;

final class CommandRegistry
{
    /** @var array<string, CommandInterface> */
    private array $commands = [];

    private bool $frozen = false;

    public function register(CommandInterface $command): void
    {
        if ($this->frozen) {
            throw new CommandRegistryFrozenException('The command registry is frozen.');
        }

        $name = self::normalizeName($command->name());
        if (isset($this->commands[$name])) {
            throw new DuplicateCommandException(sprintf('Command "%s" is already registered.', $name));
        }

        $this->commands[$name] = $command;
    }

    public function has(string $name): bool
    {
        return isset($this->commands[self::normalizeName($name)]);
    }

    public function get(string $name): ?CommandInterface
    {
        return $this->commands[self::normalizeName($name)] ?? null;
    }

    /** @return list<CommandInterface> */
    public function all(): array
    {
        return array_values($this->commands);
    }

    public function freeze(): void
    {
        $this->frozen = true;
    }

    public function isFrozen(): bool
    {
        return $this->frozen;
    }

    public function count(): int
    {
        return count($this->commands);
    }

    private static function normalizeName(string $name): string
    {
        $name = strtolower(trim($name));
        $name = ltrim($name, '-');

        if (!preg_match('/^[a-z0-9]+(?:[._-][a-z0-9]+)*$/', $name)) {
            throw new InvalidCommandNameException(sprintf('Command name "%s" is invalid.', $name));
        }

        return $name;
    }
}
