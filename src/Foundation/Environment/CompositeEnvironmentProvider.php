<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment;

use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;

final class CompositeEnvironmentProvider implements EnvironmentProviderInterface
{
    /** @var list<EnvironmentProviderInterface> */
    private array $providers;

    public function __construct(EnvironmentProviderInterface ...$providers)
    {
        $this->providers = array_values($providers);
    }

    public function has(string $key): bool
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function get(string $key, ?string $default = null): ?string
    {
        foreach ($this->providers as $provider) {
            if ($provider->has($key)) {
                return $provider->get($key);
            }
        }

        return $default;
    }

    public function all(): array
    {
        $values = [];

        for ($index = count($this->providers) - 1; $index >= 0; --$index) {
            $values = array_replace($values, $this->providers[$index]->all());
        }

        return $values;
    }
}
