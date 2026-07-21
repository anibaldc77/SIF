<?php

declare(strict_types=1);

namespace Sif\Builder\Cli\Runtime;

use Sif\Builder\Cli\Contract\VersionProviderInterface;
use Sif\Builder\Cli\Exception\CliException;

final readonly class StaticVersionProvider implements VersionProviderInterface
{
    public string $name;
    public string $release;

    public function __construct(string $applicationName, string $version)
    {
        $applicationName = trim($applicationName);
        $version = trim($version);

        if ($applicationName === '' || str_contains($applicationName, "\0")) {
            throw new CliException('Application name must be a non-empty string without null bytes.');
        }
        if ($version === '' || str_contains($version, "\0")) {
            throw new CliException('Application version must be a non-empty string without null bytes.');
        }

        $this->name = $applicationName;
        $this->release = $version;
    }

    public function applicationName(): string
    {
        return $this->name;
    }

    public function version(): string
    {
        return $this->release;
    }
}
