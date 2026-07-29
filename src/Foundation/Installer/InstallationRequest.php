<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidInstallationRequestException;

final readonly class InstallationRequest
{
    private InstallationIdentifier $identifier;

    private InstallationMode $mode;

    /** @var list<InstallationOption> */
    private array $options;

    /**
     * @param iterable<InstallationOption> $options
     */
    public function __construct(
        InstallationIdentifier $identifier,
        InstallationMode $mode,
        iterable $options = [],
    ) {
        $normalized = [];
        $seen = [];

        foreach ($options as $option) {
            if (!$option instanceof InstallationOption) {
                throw new InvalidInstallationRequestException(
                    'Installation request options must contain only InstallationOption values.',
                );
            }

            if (isset($seen[$option->name()])) {
                throw new InvalidInstallationRequestException(
                    sprintf('Duplicate installation option "%s".', $option->name()),
                );
            }

            $seen[$option->name()] = true;
            $normalized[] = $option;
        }

        $this->identifier = $identifier;
        $this->mode = $mode;
        $this->options = $normalized;
    }

    public function identifier(): InstallationIdentifier
    {
        return $this->identifier;
    }

    public function mode(): InstallationMode
    {
        return $this->mode;
    }

    /** @return list<InstallationOption> */
    public function options(): array
    {
        return $this->options;
    }

    public function hasOption(string $name): bool
    {
        return $this->option($name) !== null;
    }

    public function option(string $name): ?InstallationOption
    {
        $name = strtolower(trim($name));

        foreach ($this->options as $option) {
            if ($option->name() === $name) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @return array{
     *     identifier: string,
     *     mode: string,
     *     options: list<array{name: string, value: string|int|float|bool|null, sensitive: bool}>
     * }
     */
    public function summary(): array
    {
        return [
            'identifier' => $this->identifier->value(),
            'mode' => $this->mode->value(),
            'options' => array_map(
                static fn (InstallationOption $option): array => $option->summary(),
                $this->options,
            ),
        ];
    }
}
