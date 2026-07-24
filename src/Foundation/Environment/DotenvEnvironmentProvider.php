<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\Exceptions\DotenvSourceNotFoundException;
use Sif\Foundation\Environment\Exceptions\UnreadableDotenvSourceException;

final class DotenvEnvironmentProvider implements EnvironmentProviderInterface
{
    private readonly ArrayEnvironmentProvider $environment;

    /**
     * @param array<string, string> $values
     */
    private function __construct(array $values)
    {
        $this->environment = new ArrayEnvironmentProvider($values);
    }

    public static function fromString(
        string $contents,
        ?EnvironmentProviderInterface $fallback = null,
    ): self {
        return new self((new DotenvParser($fallback))->parse($contents));
    }

    public static function fromFile(
        string $path,
        ?EnvironmentProviderInterface $fallback = null,
    ): self {
        if (!is_file($path)) {
            throw DotenvSourceNotFoundException::forPath($path);
        }

        if (!is_readable($path)) {
            throw UnreadableDotenvSourceException::forPath($path);
        }

        $contents = file_get_contents($path);

        if ($contents === false) {
            throw UnreadableDotenvSourceException::forPath($path);
        }

        return self::fromString($contents, $fallback);
    }

    public function has(string $key): bool
    {
        return $this->environment->has($key);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $this->environment->get($key, $default);
    }

    public function all(): array
    {
        return $this->environment->all();
    }
}
