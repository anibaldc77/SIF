<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final readonly class ExampleService
{
    public function __construct(
        public string $name = 'example',
    ) {
    }
}
