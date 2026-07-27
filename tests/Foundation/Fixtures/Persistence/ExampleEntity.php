<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

final readonly class ExampleEntity
{
    public function __construct(
        public int $id,
        public string $name,
        public bool $active,
    ) {
    }
}
