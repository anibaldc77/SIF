<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Container;

final class CounterService
{
    private static int $constructed = 0;

    public function __construct()
    {
        self::$constructed++;
    }

    public static function constructed(): int
    {
        return self::$constructed;
    }

    public static function reset(): void
    {
        self::$constructed = 0;
    }
}
