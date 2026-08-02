<?php

declare(strict_types=1);

namespace Sif\Foundation\Cli\Value;

use Sif\Foundation\Cli\Exceptions\InvalidCliInvocationException;

final readonly class CliInvocation
{
    /** @var list<string> */
    private array $arguments;

    /** @var array<string, list<string|bool>> */
    private array $options;

    /** @var array<string, string> */
    private array $environment;

    private CliInteractionMode $interaction;
    private CliVerbosity $verbosity;

    /**
     * @param list<string> $arguments
     * @param array<string, list<string|bool>> $options
     * @param array<string, string> $environment
     */
    public function __construct(
        private CliCommandName $command,
        array $arguments = [],
        array $options = [],
        array $environment = [],
        ?CliInteractionMode $interaction = null,
        ?CliVerbosity $verbosity = null,
    ) {
        $this->arguments = $this->normalizeArguments($arguments);
        $this->options = $this->normalizeOptions($options);
        $this->environment = $this->normalizeEnvironment($environment);
        $this->interaction = $interaction ?? CliInteractionMode::interactive();
        $this->verbosity = $verbosity ?? CliVerbosity::normal();
    }

    public function command(): CliCommandName { return $this->command; }
    /** @return list<string> */ public function arguments(): array { return $this->arguments; }
    /** @return array<string, list<string|bool>> */ public function options(): array { return $this->options; }
    /** @return array<string, string> */ public function environment(): array { return $this->environment; }
    public function interaction(): CliInteractionMode { return $this->interaction; }
    public function verbosity(): CliVerbosity { return $this->verbosity; }
    public function argument(int $position): ?string { return $this->arguments[$position] ?? null; }
    /** @return list<string|bool> */ public function option(string $name): array { return $this->options[(new CliOptionName($name))->value()] ?? []; }
    public function hasOption(string $name): bool { return array_key_exists((new CliOptionName($name))->value(), $this->options); }
    public function environmentValue(string $name): ?string { return $this->environment[$name] ?? null; }

    /** @return array{command: string, argument_count: int, option_names: list<string>, environment_count: int, interaction: string, verbosity: string} */
    public function safeSummary(): array
    {
        return [
            'command' => $this->command->value(),
            'argument_count' => count($this->arguments),
            'option_names' => array_keys($this->options),
            'environment_count' => count($this->environment),
            'interaction' => $this->interaction->value(),
            'verbosity' => $this->verbosity->value(),
        ];
    }

    /**
     * @param list<string> $arguments
     *
     * @return list<string>
     */
    private function normalizeArguments(array $arguments): array
    {
        foreach ($arguments as $argument) {
            if (str_contains($argument, "\0")) {
                throw new InvalidCliInvocationException('CLI arguments cannot contain null bytes.');
            }
        }
        return array_values($arguments);
    }

    /**
     * @param array<string, list<string|bool>> $options
     *
     * @return array<string, list<string|bool>>
     */
    private function normalizeOptions(array $options): array
    {
        $normalized = [];
        foreach ($options as $name => $values) {
            $canonical = (new CliOptionName($name))->value();
            if ($values === []) {
                throw new InvalidCliInvocationException(sprintf('CLI option "%s" must contain at least one value.', $canonical));
            }
            foreach ($values as $value) {
                if (is_string($value) && str_contains($value, "\0")) {
                    throw new InvalidCliInvocationException('CLI option values cannot contain null bytes.');
                }
            }
            $normalized[$canonical] = array_values($values);
        }
        ksort($normalized);
        return $normalized;
    }

    /**
     * @param array<string, string> $environment
     *
     * @return array<string, string>
     */
    private function normalizeEnvironment(array $environment): array
    {
        $normalized = [];
        foreach ($environment as $name => $value) {
            if (preg_match('/^[A-Z_][A-Z0-9_]*$/', $name) !== 1 || str_contains($value, "\0")) {
                throw new InvalidCliInvocationException(sprintf('Invalid CLI environment entry "%s".', $name));
            }
            $normalized[$name] = $value;
        }
        ksort($normalized);
        return $normalized;
    }
}
