<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final readonly class AutowiredService
{
    public function __construct(
        public ExampleService $dependency,
        public string $name,
        public int $retries = 3,
        public ?CounterService $optional = null,
    ) {
    }
}
