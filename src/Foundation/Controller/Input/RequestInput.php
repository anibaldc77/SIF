<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Input;

use Sif\Foundation\Contracts\RequestInterface;
use Sif\Foundation\Controller\Argument\ActionArgumentSource;

final readonly class RequestInput
{
    /** @var array<string, array<string, mixed>> */
    private array $values;

    /** @param array<string, mixed> $body */
    public static function fromRequest(RequestInterface $request, array $body = []): self
    {
        $route = $request->attributes()->get('route.parameters', []);
        $routeValues = is_array($route) ? self::stringKeyed($route) : [];

        $headers = [];
        foreach ($request->headers()->all() as $name => $values) {
            $headers[strtolower($name)] = count($values) === 1 ? $values[0] : $values;
        }

        return new self([
            ActionArgumentSource::Route->value => $routeValues,
            ActionArgumentSource::Query->value => $request->query()->all(),
            ActionArgumentSource::Body->value => $body,
            ActionArgumentSource::Header->value => $headers,
            ActionArgumentSource::Cookie->value => $request->cookies()->all(),
            ActionArgumentSource::Attribute->value => $request->attributes()->all(),
        ]);
    }

    /** @param array<string, array<string, mixed>> $values */
    public function __construct(array $values = [])
    {
        $normalized = [];
        foreach ($values as $source => $sourceValues) {
            $normalized[$source] = self::stringKeyed($sourceValues);
        }
        $this->values = $normalized;
    }

    public function value(ActionArgumentSource $source, string $key): RequestInputValue
    {
        $lookup = $source === ActionArgumentSource::Header ? strtolower($key) : $key;
        $values = $this->values[$source->value] ?? [];

        return new RequestInputValue(
            $source,
            $key,
            array_key_exists($lookup, $values),
            $values[$lookup] ?? null,
        );
    }

    /** @return array<string, array<string, mixed>> */
    public function all(): array
    {
        return $this->values;
    }

    /**
     * @param array<array-key, mixed> $values
     *
     * @return array<string, mixed>
     */
    private static function stringKeyed(array $values): array
    {
        $normalized = [];
        foreach ($values as $key => $value) {
            if (is_string($key)) {
                $normalized[$key] = $value;
            }
        }
        return $normalized;
    }
}
