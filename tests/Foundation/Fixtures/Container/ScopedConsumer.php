<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final readonly class ScopedConsumer
{
    public function __construct(
        public CounterService $dependency,
    ) {
    }
}
