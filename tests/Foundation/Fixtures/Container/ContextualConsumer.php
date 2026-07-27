<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final readonly class ContextualConsumer
{
    public function __construct(
        public ExampleService $dependency,
    ) {
    }
}
