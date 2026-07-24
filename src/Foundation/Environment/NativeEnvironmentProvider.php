<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;
use Sif\Foundation\Environment\Exceptions\InvalidEnvironmentPrecedenceException;

final class NativeEnvironmentProvider implements EnvironmentProviderInterface
{
    public const SOURCE_ENV = 'env';
    public const SOURCE_SERVER = 'server';
    public const SOURCE_PROCESS = 'process';

    /** @var array<string, string> */
    private array $values;

    /**
     * Sources are ordered from lowest to highest precedence.
     *
     * @param list<string> $precedence
     * @param array<array-key, mixed>|null $env
     * @param array<array-key, mixed>|null $server
     * @param array<array-key, mixed>|null $process
     */
    public function __construct(
        array $precedence = [self::SOURCE_SERVER, self::SOURCE_ENV, self::SOURCE_PROCESS],
        ?array $env = null,
        ?array $server = null,
        ?array $process = null,
    ) {
        $this->assertPrecedence($precedence);

        $sources = [
            self::SOURCE_ENV => self::normalizeNativeSource($env ?? $_ENV),
            self::SOURCE_SERVER => self::normalizeNativeSource($server ?? $_SERVER),
            self::SOURCE_PROCESS => self::normalizeNativeSource($process ?? self::processEnvironment()),
        ];

        $values = [];

        foreach ($precedence as $source) {
            $values = array_replace($values, $sources[$source]);
        }

        $this->values = $values;
    }

    public function has(string $key): bool
    {
        return (new ArrayEnvironmentProvider($this->values))->has($key);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return (new ArrayEnvironmentProvider($this->values))->get($key, $default);
    }

    public function all(): array
    {
        return $this->values;
    }

    /**
     * @param list<string> $precedence
     */
    private function assertPrecedence(array $precedence): void
    {
        $expected = [self::SOURCE_ENV, self::SOURCE_PROCESS, self::SOURCE_SERVER];
        $actual = $precedence;
        sort($expected);
        sort($actual);

        if ($actual !== $expected || count($precedence) !== count(array_unique($precedence))) {
            throw InvalidEnvironmentPrecedenceException::forSources($precedence);
        }
    }

    /**
     * Native PHP sources may contain runtime metadata such as $_SERVER['argv'].
     * Those entries are not environment variables and are therefore ignored.
     *
     * @param array<array-key, mixed> $source
     *
     * @return array<string, string>
     */
    private static function normalizeNativeSource(array $source): array
    {
        $environment = [];

        foreach ($source as $key => $value) {
            if ($value === null || !is_scalar($value)) {
                continue;
            }

            $environment[(string) $key] = $value;
        }

        return ArrayEnvironmentProvider::normalize($environment);
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function processEnvironment(): array
    {
        return getenv();
    }
}
