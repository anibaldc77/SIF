<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final readonly class UnionDependencyService
{
    public function __construct(
        public ExampleService|CounterService $dependency,
    ) {
    }
}
