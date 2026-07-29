<?php

declare(strict_types=1);

namespace Sif\Foundation\Resources;

use Sif\Foundation\Resources\Exceptions\InvalidResourcePathException;

final readonly class ResourcePath
{
    private string $value;

    public function __construct(string $value)
    {
        if (str_contains($value, "\0")) {
            throw new InvalidResourcePathException('Resource paths must not contain null bytes.');
        }

        $value = str_replace('\\', '/', trim($value));
        if ($value === '' || str_starts_with($value, '/') || preg_match('/^[A-Za-z]:\//D', $value) === 1) {
            throw new InvalidResourcePathException(sprintf('Resource path "%s" must be relative.', $value));
        }

        $segments = explode('/', $value);
        foreach ($segments as $segment) {
            if ($segment === '' || $segment === '.' || $segment === '..') {
                throw new InvalidResourcePathException(sprintf('Resource path "%s" contains an unsafe segment.', $value));
            }
        }

        $this->value = implode('/', $segments);
    }

    public function value(): string
    {
        return $this->value;
    }

    /** @return list<string> */
    public function segments(): array
    {
        return explode('/', $this->value);
    }

    public function basename(): string
    {
        $segments = $this->segments();

        return $segments[array_key_last($segments)];
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
